<?php

return [
    'csp_enabled' => env('SECURITY_CSP_ENABLED', env('APP_ENV') === 'production'),
    'hsts_enabled' => env('SECURITY_HSTS_ENABLED', env('APP_ENV') === 'production'),
    'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
];
