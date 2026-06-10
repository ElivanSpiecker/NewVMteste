<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\AppConfig;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Throwable;

class GerarVideo implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 1200; // 20 min — pipeline pode demorar até ~10 min

    public int $tries = 1; // sem retry automático (VRAM é recurso escasso)

    public function __construct(public readonly int $videoId) {}

    public function handle(AppConfig $appConfig): void
    {
        $video = Video::findOrFail($this->videoId);

        $pythonPath   = $appConfig->get('videogen.python_path');
        $pipelinePath = $appConfig->get('videogen.pipeline_path');
        $outputDir    = $appConfig->get('videogen.output_dir');

        if (empty($pythonPath) || empty($pipelinePath) || empty($outputDir)) {
            $video->update([
                'status'    => 'failed',
                'erro'      => 'Pipeline não configurado. Vá em CONFIG → Pipeline e informe Python, pipeline.py e pasta de saída.',
                'progresso' => 0,
            ]);
            return;
        }

        $steps = [
            'generating_script'    => 17,
            'generating_images'    => 33,
            'generating_narration' => 50,
            'generating_music'     => 67,
            'generating_subtitles' => 83,
            'assembling'           => 95,
        ];

        $video->update(['status' => 'generating_script', 'progresso' => 5]);

        $uploadDir = storage_path("app/uploads/{$video->id}");

        $command = [
            $pythonPath,
            '-u', // unbuffered: marcadores STEP: chegam em tempo real
            $pipelinePath,
            '--tema',           $video->tema,
            '--duracao',        (string) $video->duracao,
            '--video-id',       (string) $video->id,
            '--output-dir',     $outputDir,
            '--idioma',         $video->idioma ?? 'PT-BR',
            '--voz',            $video->voz ?? '',
            '--imagens-modo',   $video->imagens_modo,
            '--narracao-modo',  $video->narracao_modo,
            '--musica-modo',    $video->musica_modo,
            '--legendas-modo',  $video->legendas_modo,
        ];

        if ($video->imagens_modo === 'upload') {
            $arquivos = glob("{$uploadDir}/imagens/cena*.*") ?: [];
            sort($arquivos);
            $command[] = '--imagens-upload';
            $command[] = implode(';', $arquivos);
        }

        if ($video->narracao_modo === 'upload') {
            $arquivos = glob("{$uploadDir}/narracao.*") ?: [];
            $command[] = '--narracao-upload';
            $command[] = $arquivos[0] ?? '';
        }

        if ($video->musica_modo === 'upload') {
            $arquivos = glob("{$uploadDir}/musica.*") ?: [];
            $command[] = '--musica-upload';
            $command[] = $arquivos[0] ?? '';
        }

        if ($video->legendas_modo === 'upload') {
            $arquivos = glob("{$uploadDir}/legenda.*") ?: [];
            $command[] = '--legendas-upload';
            $command[] = $arquivos[0] ?? '';
        }

        $process = new Process(
            command: $command,
            // Herda o ambiente completo (SystemRoot/PATH são essenciais no Windows —
            // sem SystemRoot o Python falha em _Py_HashRandomization_Init) e só
            // sobrescreve o encoding (sem isso, emojis quebram em cp1252).
            env: array_merge(getenv(), [
                'PYTHONIOENCODING' => 'utf-8',
                'PYTHONUTF8'       => '1',
            ]),
        );

        $process->setTimeout(1200);

        $currentStep = 'generating_script';
        $artifacts = ['narracao' => null, 'musica' => null, 'imagens' => []];

        $process->run(function (string $type, string $buffer) use ($video, $steps, &$currentStep, &$artifacts): void {
            foreach ($steps as $step => $progresso) {
                if (str_contains($buffer, "STEP:{$step}")) {
                    $currentStep = $step;
                    $video->update(['status' => $step, 'progresso' => $progresso]);
                    break;
                }
            }

            // Parse ARTIFACT: markers
            if (preg_match_all('/ARTIFACT:([a-z]+)(?::(\d+))?=(.+)/', $buffer, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $m) {
                    $kind = $m[1];
                    $idx  = $m[2] !== '' ? (int) $m[2] : null;
                    $path = trim($m[3]);

                    if ($kind === 'imagem' && $idx !== null) {
                        $artifacts['imagens'][$idx] = $path;
                    } elseif (in_array($kind, ['narracao', 'musica'], true)) {
                        $artifacts[$kind] = $path;
                    }
                }
            }
        });

        if (!$process->isSuccessful()) {
            $video->update([
                'status'  => 'failed',
                'progresso' => 0,
                'erro'    => $process->getErrorOutput() ?: $process->getOutput(),
            ]);
            return;
        }

        $videoFile = "{$outputDir}\\video\\final_{$video->id}.mp4";
        $srtFile   = "{$outputDir}\\audio\\legenda.srt";

        // Copia artefatos para diretório persistente por vídeo
        $persisted = $this->persistArtifacts($video->id, $artifacts);

        // Thumbnail = primeira imagem persistida (cena01).
        // Fallback: cena01 no diretório de upload (caso o pipeline não emita ARTIFACT:imagem).
        $thumbnail = $persisted['imagens'][0] ?? null;
        if (!$thumbnail) {
            $uploadCena1 = glob("{$uploadDir}/imagens/cena01.*") ?: [];
            $thumbnail = $uploadCena1[0] ?? null;
        }

        $video->update([
            'status'         => 'done',
            'progresso'      => 100,
            'video_path'     => file_exists($videoFile) ? $videoFile : null,
            'srt_path'       => file_exists($srtFile)   ? $srtFile   : null,
            'narracao_path'  => $persisted['narracao'],
            'musica_path'    => $persisted['musica'],
            'imagens_paths'  => $persisted['imagens'] ?: null,
            'thumbnail_path' => $thumbnail && file_exists($thumbnail) ? $thumbnail : null,
        ]);
    }

    /**
     * Copia narração/música/imagens do diretório de output (que será sobrescrito
     * pelo próximo vídeo) para storage/app/artifacts/{id}/, devolvendo paths absolutos.
     */
    private function persistArtifacts(int $videoId, array $artifacts): array
    {
        $base = storage_path("app/artifacts/{$videoId}");
        if (!is_dir($base)) {
            mkdir($base, 0755, true);
        }

        $out = ['narracao' => null, 'musica' => null, 'imagens' => []];

        if ($artifacts['narracao'] && file_exists($artifacts['narracao'])) {
            $dest = "{$base}\\narracao.wav";
            @copy($artifacts['narracao'], $dest);
            $out['narracao'] = $dest;
        }

        if ($artifacts['musica'] && file_exists($artifacts['musica'])) {
            $ext = strtolower(pathinfo($artifacts['musica'], PATHINFO_EXTENSION) ?: 'mp3');
            $dest = "{$base}\\musica.{$ext}";
            @copy($artifacts['musica'], $dest);
            $out['musica'] = $dest;
        }

        if (!empty($artifacts['imagens'])) {
            $destDir = "{$base}\\imagens";
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
            ksort($artifacts['imagens']);
            foreach ($artifacts['imagens'] as $idx => $src) {
                if (!file_exists($src)) {
                    continue;
                }
                $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION) ?: 'png');
                $dest = sprintf('%s\\cena%02d.%s', $destDir, $idx, $ext);
                @copy($src, $dest);
                $out['imagens'][] = $dest;
            }
        }

        return $out;
    }

    public function failed(Throwable $exception): void
    {
        Video::where('id', $this->videoId)->update([
            'status' => 'failed',
            'erro'   => $exception->getMessage(),
        ]);
    }
}
