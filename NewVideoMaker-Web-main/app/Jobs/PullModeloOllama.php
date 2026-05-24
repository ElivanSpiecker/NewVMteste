<?php

namespace App\Jobs;

use App\Models\SetupTask;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Faz `ollama pull <model>` via API HTTP do Ollama (com streaming).
 *
 * Por que API HTTP em vez de invocar o CLI: o endpoint /api/pull do Ollama
 * retorna chunks JSON com progresso (total/completed em bytes), o que da
 * pra refletir na UI em tempo real. CLI tambem mostra, mas ler texto
 * com barra de progresso ANSI e mais chato.
 */
class PullModeloOllama implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $timeout = 1800; // 30 min (modelos grandes)
    public int $tries   = 1;

    public function __construct(public readonly int $taskId, public readonly string $modelName)
    {
    }

    public function handle(): void
    {
        $task = SetupTask::findOrFail($this->taskId);
        $task->update(['status' => 'running', 'progresso' => 1, 'mensagem' => "Baixando {$this->modelName}..."]);

        try {
            if (!$this->isPortOpen('127.0.0.1', 11434)) {
                throw new \RuntimeException('Ollama nao esta rodando na porta 11434. Instale e inicie o Ollama primeiro.');
            }

            // POST streaming pro /api/pull. Cada chunk JSON traz {status, total, completed}
            $url = 'http://127.0.0.1:11434/api/pull';
            $body = json_encode(['name' => $this->modelName, 'stream' => true]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST            => true,
                CURLOPT_HTTPHEADER      => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS      => $body,
                CURLOPT_TIMEOUT         => 1800,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_WRITEFUNCTION   => function ($ch, $chunk) use ($task) {
                    // O Ollama serve uma sequencia de objetos JSON delimitados por \n
                    foreach (explode("\n", trim($chunk)) as $line) {
                        if ($line === '') continue;
                        $event = json_decode($line, true);
                        if (!is_array($event)) continue;

                        $status   = $event['status']    ?? '';
                        $total    = (int) ($event['total']     ?? 0);
                        $complete = (int) ($event['completed'] ?? 0);

                        $pct = ($total > 0) ? min(99, (int) floor(($complete / $total) * 100)) : null;

                        $task->update([
                            'progresso' => $pct ?? $task->progresso,
                            'mensagem'  => $status ?: 'baixando...',
                        ]);

                        if (str_contains($status, 'error')) {
                            throw new \RuntimeException("Ollama: $status");
                        }
                    }
                    return strlen($chunk);
                },
            ]);

            $ok = curl_exec($ch);
            $err = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($ok === false) {
                throw new \RuntimeException("Falha no pull: $err");
            }
            if ($code >= 400) {
                throw new \RuntimeException("Ollama retornou HTTP $code");
            }

            $task->update(['status' => 'done', 'progresso' => 100, 'mensagem' => 'Modelo pronto.']);
        } catch (Throwable $e) {
            $task->update(['status' => 'failed', 'erro' => $e->getMessage()]);
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
