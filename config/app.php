<?php

return [
    'name' => env('APP_NAME', 'PHP-PDNSManager'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'), // DevSkim: ignore DS162092, DS137138
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    'secret' => env('APP_SECRET'),
    'log_path' => __DIR__ . '/../storage/logs',
    'log_level' => env('LOG_LEVEL', 'warning'),
];
