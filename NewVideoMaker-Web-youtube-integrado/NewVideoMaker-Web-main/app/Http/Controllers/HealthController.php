<?php

namespace App\Http\Controllers;

class HealthController extends Controller
{
    private array $services = [
        ['name' => 'Ollama (Gemma 4)',  'host' => '127.0.0.1', 'port' => 11434],
        ['name' => 'ComfyUI (FLUX)',    'host' => '127.0.0.1', 'port' => 8188],
        ['name' => 'ACE-Step (Música)', 'host' => '127.0.0.1', 'port' => 7860],
    ];

    public function index(): \Illuminate\View\View
    {
        return view('health.index', [
            'services' => $this->check(),
        ]);
    }

    public function api(): \Illuminate\Http\JsonResponse
    {
        $results = $this->check();
        $allUp   = collect($results)->every(fn($s) => $s['up']);

        return response()->json([
            'all_up'   => $allUp,
            'services' => $results,
        ]);
    }

    private function check(): array
    {
        return array_map(function (array $service): array {
            $up = $this->ping($service['host'], $service['port']);
            return [
                'name' => $service['name'],
                'host' => $service['host'],
                'port' => $service['port'],
                'up'   => $up,
            ];
        }, $this->services);
    }

    private function ping(string $host, int $port, int $timeoutMs = 500): bool
    {
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeoutMs / 1000);
        if ($socket) {
            fclose($socket);
            return true;
        }
        return false;
    }
}
