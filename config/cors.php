<?php

return [
    'paths' => ['admin-api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://admin.unigo-bd.com'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept', 'Content-Type', 'X-Requested-With', 'X-XSRF-TOKEN'],
    'exposed_headers' => [],
    'max_age' => 600,
    'supports_credentials' => true,
];
