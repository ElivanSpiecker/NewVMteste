<?php

namespace App\Jobs;

use App\Models\Video;
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

    public function handle(): void
    {
        $video = Video::findOrFail($this->videoId);

        $pythonPath = config('videogen.python_path');
        $pipelinePath = config('videogen.pipeline_path');
        $outputDir = config('videogen.output_dir');

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
            env: [
                'PYTHONIOENCODING' => 'utf-8', // sem isso, emojis quebram em cp1252
                'PYTHONUTF8'       => '1',
            ],
        );

        $process->setTimeout(1200);

        $currentStep = 'generating_script';

        $process->run(function (string $type, string $buffer) use ($video, $steps, &$currentStep): void {
            foreach ($steps as $step => $progresso) {
                if (str_contains($buffer, "STEP:{$step}")) {
                    $currentStep = $step;
                    $video->update(['status' => $step, 'progresso' => $progresso]);
                    break;
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

        $video->update([
            'status'     => 'done',
            'progresso'  => 100,
            'video_path' => file_exists($videoFile) ? $videoFile : null,
            'srt_path'   => file_exists($srtFile)   ? $srtFile   : null,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Video::where('id', $this->videoId)->update([
            'status' => 'failed',
            'erro'   => $exception->getMessage(),
        ]);
    }
}
