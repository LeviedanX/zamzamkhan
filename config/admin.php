<?php

return [
    // Login hanya dibuka sebentar setelah tombol admin di homepage dikirim.
    'login_entry_ttl_seconds' => 600,
    'session_idle_seconds' => (int) env('ADMIN_SESSION_IDLE_SECONDS', 1800),
    'session_absolute_seconds' => (int) env('ADMIN_SESSION_ABSOLUTE_SECONDS', 28800),
    'block_gambling_content' => env('ADMIN_BLOCK_GAMBLING_CONTENT', true),
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
