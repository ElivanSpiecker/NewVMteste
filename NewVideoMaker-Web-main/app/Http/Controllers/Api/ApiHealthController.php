<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiHealthController extends Controller
{
    private array $services = [
        ['name' => 'Ollama (Gemma 4)',  'host' => '127.0.0.1', 'port' => 11434],
        ['name' => 'ComfyUI (FLUX)',    'host' => '127.0.0.1', 'port' => 8188],
    ];

    public function index(): JsonResponse
    {
        $results = array_map(function (array $service): array {
            return [
                'name' => $service['name'],
                'host' => $service['host'],
                'port' => $service['port'],
                'up'   => $this->ping($service['host'], $service['port']),
            ];
        }, $this->services);

        return response()->json([
            'all_up'   => collect($results)->every(fn($s) => $s['up']),
            'services' => $results,
        ]);
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
