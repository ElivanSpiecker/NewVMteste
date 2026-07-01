<?php

namespace App\Http\Controllers;

use App\Services\AppConfig;

class HealthController extends Controller
{
    /**
     * Definição dos serviços externos que o pipeline depende.
     * Cada um tem um endpoint HTTP que retorna 200 quando o servidor está pronto.
     */
    private array $services = [
        [
            'key'     => 'ollama',
            'name'    => 'Ollama (Gemma)',
            'host'    => '127.0.0.1',
            'port'    => 11434,
            'probe'   => '/api/tags', // GET retorna 200 com lista de modelos
            'install' => 'https://ollama.com/download/windows',
        ],
        [
            'key'     => 'comfyui',
            'name'    => 'ComfyUI (FLUX)',
            'host'    => '127.0.0.1',
            'port'    => 8188,
            'probe'   => '/system_stats',
            'install' => 'https://github.com/comfyanonymous/ComfyUI/releases',
        ],
    ];

    public function __construct(private readonly AppConfig $appConfig)
    {
    }

    public function index(): \Illuminate\View\View
    {
        return view('health.index', [
            'services' => $this->check(),
            'pipeline' => $this->checkPipelinePaths(),
        ]);
    }

    public function api(): \Illuminate\Http\JsonResponse
    {
        $results  = $this->check();
        $pipeline = $this->checkPipelinePaths();

        $allUp = collect($results)->every(fn($s) => $s['up']) && $pipeline['ok'];

        return response()->json([
            'all_up'   => $allUp,
            'services' => $results,
            'pipeline' => $pipeline,
        ]);
    }

    public function check(): array
    {
        return array_map(function (array $service): array {
            [$up, $detail] = $this->ping($service['host'], $service['port'], $service['probe']);
            return [
                'key'     => $service['key'],
                'name'    => $service['name'],
                'host'    => $service['host'],
                'port'    => $service['port'],
                'up'      => $up,
                'detail'  => $detail,
                'install' => $service['install'],
            ];
        }, $this->services);
    }

    /**
     * Valida se os caminhos do pipeline existem no disco.
     */
    public function checkPipelinePaths(): array
    {
        $python   = $this->appConfig->get('videogen.python_path');
        $pipeline = $this->appConfig->get('videogen.pipeline_path');
        $output   = $this->appConfig->get('videogen.output_dir');

        $pythonOk   = $python   && is_file($python);
        $pipelineOk = $pipeline && is_file($pipeline);
        $outputOk   = $output   && is_dir($output);

        return [
            'ok'           => $pythonOk && $pipelineOk && $outputOk,
            'python'       => ['path' => $python,   'ok' => $pythonOk,   'reason' => $pythonOk   ? null : ($python   ? 'Arquivo não encontrado.' : 'Não configurado.')],
            'pipeline'     => ['path' => $pipeline, 'ok' => $pipelineOk, 'reason' => $pipelineOk ? null : ($pipeline ? 'Arquivo não encontrado.' : 'Não configurado.')],
            'output_dir'   => ['path' => $output,   'ok' => $outputOk,   'reason' => $outputOk   ? null : ($output   ? 'Pasta não encontrada.'   : 'Não configurado.')],
        ];
    }

    /**
     * Testa o serviço com TCP + GET HTTP no probe path. Retorna [bool, mensagem].
     */
    private function ping(string $host, int $port, string $probe, int $timeoutMs = 800): array
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeoutMs / 1000);
        if (!$socket) {
            return [false, "Porta {$port} fechada — serviço não está rodando."];
        }
        fclose($socket);

        // Faz uma requisição HTTP curta para garantir que é o serviço esperado, não outro processo
        $url = "http://{$host}:{$port}{$probe}";
        $ctx = stream_context_create(['http' => ['timeout' => $timeoutMs / 1000, 'ignore_errors' => true]]);
        $body = @file_get_contents($url, false, $ctx);

        if ($body === false) {
            return [true, 'Porta aberta, mas serviço não respondeu ao probe HTTP.'];
        }
        return [true, null];
    }
}
