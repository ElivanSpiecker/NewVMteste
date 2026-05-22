<?php

return [
    /*
     * Python do venv do NewVideoMaker (onde estão os scripts do pipeline).
     */
    'python_path' => env(
        'VIDEOGEN_PYTHON',
        'C:\\Users\\nicol\\PycharmProjects\\NewVideoMaker\\.venv\\Scripts\\python.exe'
    ),

    /*
     * Caminho absoluto para o pipeline.py adaptado para receber argumentos CLI.
     */
    'pipeline_path' => env(
        'VIDEOGEN_PIPELINE',
        'C:\\Users\\nicol\\PycharmProjects\\NewVideoMaker\\pipeline.py'
    ),

    /*
     * Diretório onde o pipeline salva imagens, áudio e vídeo (subpastas video/, audio/, imagens/).
     */
    'output_dir' => env(
        'VIDEOGEN_OUTPUT_DIR',
        'C:\\Users\\nicol\\PycharmProjects\\NewVideoMaker\\output'
    ),

    /*
     * Python do venv do Kokoro (onde edge_tts está instalado — usado para preview de vozes).
     */
    'python_kokoro' => env(
        'VIDEOGEN_PYTHON_KOKORO',
        'C:\\Users\\nicol\\PycharmProjects\\Kokoro\\venv\\Scripts\\python.exe'
    ),

    /*
     * Pasta dos scripts Python do pipeline (gerar_narracao.py, gerar_amostra_voz.py, etc.).
     */
    'scripts_dir' => env(
        'VIDEOGEN_SCRIPTS_DIR',
        'C:\\Users\\nicol\\PycharmProjects\\NewVideoMaker\\scripts'
    ),
];
