<?php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['POST', 'GET', 'OPTIONS'],
    'allowed_origins' => [env('ALLOWED_ORIGIN', '')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Content-Type', 'X-Requested-With'],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => false,
];
