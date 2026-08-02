<?php
return [
    'csp_enabled' => (bool) env('SECURITY_CSP_ENABLED', true),
    'rate_limit' => [
        'enabled' => (bool) env('SECURITY_RATE_LIMIT_ENABLED', true),
        'requests' => (int) env('SECURITY_RATE_LIMIT_REQUESTS', 100),
        'window' => (int) env('SECURITY_RATE_LIMIT_WINDOW', 60),
    ],
    'mfa_enabled' => (bool) env('SECURITY_MFA_ENABLED', false),
    'brute_force_threshold' => (int) env('SECURITY_BRUTE_FORCE_THRESHOLD', 5),
];
