<?php

return [
    // DevSkim: ignore DS137138 - Default local PowerDNS API URL
    'api_url' => env('PDNS_API_URL', 'https://127.0.0.1:8081'),
    'api_key' => env('PDNS_API_KEY'),
    'version' => env('PDNS_VERSION', '4.9'),
    'timeout' => (int) env('PDNS_TIMEOUT', 30),
    'verify_ssl' => (bool) env('PDNS_VERIFY_SSL', true),
    // DevSkim: ignore DS137138 - PowerDNS standard default server ID
    'server_id' => env('PDNS_SERVER_ID', 'localhost'),
];
