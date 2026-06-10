<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;

class VoiceController extends Controller
{
    /**
     * Vozes disponíveis por idioma.
     * Fonte única de verdade para o frontend e a validação.
     */
    public const VOICES = [
        'PT-BR' => [
            ['id' => 'pf_dora',  'name' => 'Dora',  'gender' => 'Feminino'],
            ['id' => 'pm_alex',  'name' => 'Alex',  'gender' => 'Masculino'],
            ['id' => 'pm_santa', 'name' => 'Santa', 'gender' => 'Masculino'],
        ],
        'EN-US' => [
            ['id' => 'af_heart',   'name' => 'Heart',   'gender' => 'Female'],
            ['id' => 'af_bella',   'name' => 'Bella',   'gender' => 'Female'],
            ['id' => 'am_michael', 'name' => 'Michael', 'gender' => 'Male'],
        ],
    ];

    /**
     * Lista de todos os voice IDs válidos (para validação rápida).
     */
    public static function allVoiceIds(): array
    {
        $ids = [];
        foreach (self::VOICES as $voices) {
            foreach ($voices as $v) {
                $ids[] = $v['id'];
            }
        }
        return $ids;
    }

    /**
     * GET /api/voices — retorna vozes agrupadas por idioma.
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => self::VOICES]);
    }

    /**
     * GET /api/voices/{voiceId}/preview — gera (com cache) e serve amostra MP3.
     */
    public function preview(string $voiceId): BinaryFileResponse|JsonResponse
    {
        // Valida que o voice ID existe no catálogo
        if (!in_array($voiceId, self::allVoiceIds(), true)) {
            return response()->json(['error' => 'Voz não encontrada.'], 404);
        }

        $cacheDir = storage_path('app/voice-samples');
        $mp3Path = "{$cacheDir}/{$voiceId}.mp3";

        // Se já tem cache, serve direto
        if (file_exists($mp3Path)) {
            return response()->file($mp3Path, [
                'Content-Type' => 'audio/mpeg',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        // Gera a amostra via Python (Kokoro TTS, local)
        $pythonKokoro = config('videogen.python_kokoro',
            'C:\\Users\\nicol\\PycharmProjects\\Kokoro\\venv\\Scripts\\python.exe'
        );
        $scriptPath = config('videogen.scripts_dir',
            'C:\\Users\\nicol\\PycharmProjects\\NewVideoMaker\\scripts'
        ) . '\\gerar_amostra_voz.py';

        $process = new Process(
            command: [$pythonKokoro, $scriptPath, $voiceId, $mp3Path],
            // Herda o ambiente completo (SystemRoot/PATH são essenciais no Windows —
            // sem SystemRoot o Python falha em _Py_HashRandomization_Init) e só
            // sobrescreve o encoding.
            env: array_merge(getenv(), [
                'PYTHONIOENCODING' => 'utf-8',
                'PYTHONUTF8'       => '1',
            ]),
        );
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful() || !file_exists($mp3Path)) {
            return response()->json([
                'error' => 'Falha ao gerar amostra de voz.',
                'detail' => $process->getErrorOutput() ?: $process->getOutput(),
            ], 500);
        }

        return response()->file($mp3Path, [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
