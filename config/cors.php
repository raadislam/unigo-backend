<?php

return [
    'paths' => ['api/*', 'admin-api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://unigo-bd.com',
        'https://www.unigo-bd.com',
        'https://admin.unigo-bd.com',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => true,
];
