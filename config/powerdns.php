<?php
return [
    'api_url' => env('PDNS_API_URL', 'http://127.0.0.1:8081'),
    'api_key' => env('PDNS_API_KEY'),
    'version' => env('PDNS_VERSION', '4.9'),
    'timeout' => (int) env('PDNS_TIMEOUT', 30),
    'verify_ssl' => (bool) env('PDNS_VERIFY_SSL', true),
    'server_id' => env('PDNS_SERVER_ID', 'localhost'),
];
