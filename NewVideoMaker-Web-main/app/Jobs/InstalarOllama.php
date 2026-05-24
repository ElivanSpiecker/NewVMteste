<?php

namespace App\Jobs;

use App\Models\SetupTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Baixa o instalador oficial do Ollama (OllamaSetup.exe) e roda silenciosamente.
 *
 * O instalador oficial e auto-extraivel; suporta /SILENT (Inno por baixo).
 * Roda no perfil do usuario (nao precisa admin). Apos terminar, Ollama abre
 * a porta 11434 automaticamente como Windows Service do usuario.
 */
class InstalarOllama implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 900; // 15 min
    public int $tries   = 1;

    private const DOWNLOAD_URL = 'https://ollama.com/download/OllamaSetup.exe';

    public function __construct(public readonly int $taskId)
    {
    }

    public function handle(): void
    {
        $task = SetupTask::findOrFail($this->taskId);
        $task->update(['status' => 'running', 'progresso' => 5, 'mensagem' => 'Iniciando download...']);

        try {
            $dest = storage_path('app/downloads/OllamaSetup.exe');
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0755, true);
            }

            // 1) Download — Laravel HTTP client com streaming
            $task->update(['mensagem' => 'Baixando OllamaSetup.exe...']);
            $response = Http::timeout(600)->sink($dest)->get(self::DOWNLOAD_URL);
            if ($response->failed()) {
                throw new \RuntimeException('Download falhou: HTTP '.$response->status());
            }

            $size = is_file($dest) ? filesize($dest) : 0;
            if ($size < 10 * 1024 * 1024) { // sanity: Ollama installer tem >100 MB
                throw new \RuntimeException("Arquivo baixado parece corrompido ({$size} bytes).");
            }
            $task->update(['progresso' => 50, 'mensagem' => 'Download OK. Instalando silenciosamente...']);

            // 2) Silent install
            $process = new Process([$dest, '/SILENT', '/NORESTART']);
            $process->setTimeout(600);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \RuntimeException(
                    'Instalador falhou: '.($process->getErrorOutput() ?: $process->getOutput())
                );
            }

            $task->update(['progresso' => 80, 'mensagem' => 'Aguardando Ollama subir na porta 11434...']);

            // 3) Aguardar o servico subir (instala como background service do user)
            $deadline = time() + 60;
            while (time() < $deadline) {
                if ($this->isPortOpen('127.0.0.1', 11434)) {
                    break;
                }
                sleep(1);
            }

            $task->update([
                'status'    => 'done',
                'progresso' => 100,
                'mensagem'  => 'Ollama instalado e rodando.',
            ]);
        } catch (Throwable $e) {
            $task->update([
                'status'    => 'failed',
                'progresso' => 0,
                'erro'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        SetupTask::where('id', $this->taskId)->update([
            'status' => 'failed',
            'erro'   => $exception->getMessage(),
        ]);
    }

    private function isPortOpen(string $host, int $port, float $timeoutSec = 0.5): bool
    {
        $sock = @fsockopen($host, $port, $errno, $errstr, $timeoutSec);
        if ($sock) { fclose($sock); return true; }
        return false;
    }
}
