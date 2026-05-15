<?php

namespace App\Http\Controllers;

use App\Jobs\PublicarYoutubeShort;
use App\Models\Video;
use App\Models\YoutubeAccount;
use App\Models\YoutubeUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class YoutubeShortsController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $accounts = YoutubeAccount::orderBy('display_name')->get();
        $uploads  = YoutubeUpload::with('account', 'video')->latest()->get();

        return view('shorts.index', compact('accounts', 'uploads'));
    }

    public function create(): \Illuminate\View\View
    {
        $accounts = YoutubeAccount::orderBy('display_name')->get();
        $videosProntos = Video::query()
            ->where('status', 'done')
            ->whereNotNull('video_path')
            ->latest()
            ->get(['id', 'tema', 'duracao', 'video_path']);

        return view('shorts.create', compact('accounts', 'videosProntos'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Rate limiting: máx 10 publicações/agendamentos por hora por IP — protege a cota da YouTube API
        $key = 'shorts-store:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Muitas tentativas. Tente novamente em {$seconds}s.")->withInput();
        }
        RateLimiter::hit($key, 3600);

        $accountIds = YoutubeAccount::query()->pluck('id')->all();

        $data = $request->validate([
            'youtube_account_id' => ['required', Rule::in($accountIds)],
            'origem'             => ['required', 'in:upload,existente'],
            'video_id'           => ['required_if:origem,existente', 'nullable', 'integer', Rule::exists('videos', 'id')],
            'video'              => [
                'required_if:origem,upload',
                'nullable',
                'file',
                'mimes:mp4,mov,avi,wmv,mpeg,mpg,webm,mkv,flv,3gp',
                'max:262144', // 256 MB no upload local; YouTube aceita até 256 GB no canal verificado
            ],
            'title'         => ['required', 'string', 'max:100'],
            'description'   => ['nullable', 'string', 'max:5000'],
            'tags'          => ['nullable', 'string', 'max:500'],
            'category_id'   => ['nullable', 'string', 'max:5'],
            'privacy_status' => ['required', Rule::in(['private', 'unlisted', 'public'])],
            'made_for_kids' => ['nullable', 'boolean'],
            'modo'          => ['required', 'in:agora,agendar'],
            'scheduled_at'  => ['required_if:modo,agendar', 'nullable', 'date', 'after:+5 minutes'],
        ], [
            'video.mimes'         => 'Formato de vídeo não aceito. Use MP4, MOV, AVI, WMV, MPEG, WEBM, MKV, FLV ou 3GP.',
            'video.max'           => 'O arquivo excede 256 MB.',
            'scheduled_at.after'  => 'O agendamento precisa ser pelo menos 5 minutos no futuro.',
            'title.max'           => 'O título deve ter no máximo 100 caracteres (limite do YouTube).',
            'description.max'     => 'A descrição deve ter no máximo 5000 caracteres (limite do YouTube).',
            'tags.max'            => 'O conjunto de tags deve ter no máximo 500 caracteres (limite do YouTube).',
        ]);

        $account = YoutubeAccount::findOrFail($data['youtube_account_id']);

        // Resolve o caminho do arquivo MP4 — vindo do pipeline ou de upload manual
        $sourcePath = null;
        $videoModelId = null;

        if ($data['origem'] === 'existente') {
            $video = Video::findOrFail($data['video_id']);
            if (!$video->isDone() || !$video->video_path || !is_file($video->video_path)) {
                throw ValidationException::withMessages([
                    'video_id' => 'O vídeo selecionado não tem arquivo MP4 disponível.',
                ]);
            }
            $sourcePath = $video->video_path;
            $videoModelId = $video->id;
        } else {
            $file = $request->file('video');
            $destDir = storage_path('app/youtube-uploads');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            $ext = strtolower($file->getClientOriginalExtension() ?: 'mp4');
            $filename = uniqid('short_', true).'.'.$ext;
            $file->move($destDir, $filename);
            $sourcePath = $destDir.DIRECTORY_SEPARATOR.$filename;
        }

        $scheduledAt = null;
        if ($data['modo'] === 'agendar') {
            $scheduledAt = Carbon::parse($data['scheduled_at']);
        }

        $upload = YoutubeUpload::create([
            'youtube_account_id' => $account->id,
            'video_id'           => $videoModelId,
            'title'              => trim($data['title']),
            'description'        => $data['description'] ?? null,
            'tags'               => $this->normalizeTags($data['tags'] ?? null),
            'category_id'        => $data['category_id'] ?? '22',
            'privacy_status'     => $data['privacy_status'],
            'made_for_kids'      => (bool) ($data['made_for_kids'] ?? false),
            'scheduled_at'       => $scheduledAt,
            'source_path'        => $sourcePath,
            'status'             => 'pending',
            'progresso'          => 0,
        ]);

        // Sempre faz upload IMEDIATO. Para agendados, o YouTube recebe `publishAt`
        // junto do upload e fica responsável por tornar o vídeo público no horário.
        // Isso evita depender de o worker do Laravel estar de pé na hora marcada.
        PublicarYoutubeShort::dispatch($upload->id)->onQueue('youtube');

        return redirect()->route('shorts.show', $upload)
            ->with('success', $scheduledAt
                ? 'Upload iniciado. O YouTube publicará automaticamente em '.$scheduledAt->format('d/m/Y H:i').'.'
                : 'Publicação iniciada. Acompanhe o status abaixo.');
    }

    public function show(YoutubeUpload $short): \Illuminate\View\View
    {
        $short->load('account', 'video');
        return view('shorts.show', ['upload' => $short]);
    }

    public function poll(YoutubeUpload $short): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'      => $short->status,
            'statusLabel' => $short->statusLabel(),
            'progresso'   => $short->progresso,
            'erro'        => $short->erro,
            'youtubeUrl'  => $short->youtubeUrl(),
            'youtubeId'   => $short->youtube_video_id,
        ]);
    }

    public function destroy(YoutubeUpload $short): RedirectResponse
    {
        // Apaga apenas o arquivo se ele foi uploadado manualmente (fora da pasta do pipeline)
        if ($short->source_path && str_contains($short->source_path, 'youtube-uploads') && is_file($short->source_path)) {
            @unlink($short->source_path);
        }
        $short->delete();
        return redirect()->route('shorts.index')->with('success', 'Registro removido.');
    }

    private function normalizeTags(?string $raw): ?string
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $tags = array_filter(array_map('trim', explode(',', $raw)));
        return $tags ? implode(',', $tags) : null;
    }
}
