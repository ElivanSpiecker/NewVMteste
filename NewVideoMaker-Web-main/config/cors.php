<?php

return [
    /*
     * Aplicado em /api/* e em download/legenda (acessados pelo front Lovable).
     * Para produção, restrinja `allowed_origins` ao domínio real do front.
     */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [
        '#^https?://([a-z0-9-]+\.)?lovable\.app$#i',
        '#^https?://([a-z0-9-]+\.)?lovable\.dev$#i',
        '#^https?://localhost(:\d+)?$#i',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
