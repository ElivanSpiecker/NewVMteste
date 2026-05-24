<?php

/*
 * Defaults do pipeline de geração de vídeo.
 *
 * Estes valores são fallback do .env. NÃO use paths absolutos hardcoded aqui --
 * o cliente final configura tudo via UI em /config, e os defaults precisam
 * ficar vazios para que AppConfig::pendencies() detecte que falta configurar.
 */
return [
    'python_path'   => env('VIDEOGEN_PYTHON', ''),
    'pipeline_path' => env('VIDEOGEN_PIPELINE', ''),
    'output_dir'    => env('VIDEOGEN_OUTPUT_DIR', ''),

    /*
     * Python do venv do Kokoro (onde edge_tts está instalado — usado para preview de vozes).
     */
    'python_kokoro' => env('VIDEOGEN_PYTHON_KOKORO', ''),

    /*
     * Pasta dos scripts Python do pipeline (gerar_narracao.py, gerar_amostra_voz.py, etc.).
     */
    'scripts_dir' => env('VIDEOGEN_SCRIPTS_DIR', ''),
];
