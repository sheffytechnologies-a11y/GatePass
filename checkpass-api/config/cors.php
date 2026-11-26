<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],
    
    'allowed_origins' => explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        'https://checkpass.ng,https://www.checkpass.ng,https://api.checkpass.ng,https://localhost,capacitor://localhost,http://localhost:3100,http://localhost:5173,http://127.0.0.1:3100,http://127.0.0.1:5173,http://127.0.0.1:8000'
    )),

    // 'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://localhost:8100,http://127.0.0.1:5173,http://127.0.0.1:8100')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
