<?php

return [
    'seed' => [
        'name' => env('ADMIN_NAME', 'Administrator PT Zam Zam Khan'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],
    'retention' => [
        'web_visits_days' => env('WEB_VISIT_RETENTION_DAYS', 400),
        'report_exports_days' => env('REPORT_EXPORT_RETENTION_DAYS', 30),
    ],
];
