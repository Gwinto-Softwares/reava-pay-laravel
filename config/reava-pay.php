<?php

return [

    'key' => env('REAVA_PAY_KEY'),
    'public_key' => env('REAVA_PAY_PUBLIC_KEY'),
    'webhook_secret' => env('REAVA_PAY_WEBHOOK_SECRET'),
    'base_url' => env('REAVA_PAY_BASE_URL', 'https://reavapay.com/api/v1'),
    'timeout' => 30,
    'queue' => env('REAVA_PAY_QUEUE', 'default'),
    'webhook_path' => env('REAVA_PAY_WEBHOOK_PATH', 'webhooks/reava-pay'),

    // Admin panel — customize middleware/layout to match your app
    'admin_prefix' => env('REAVA_PAY_ADMIN_PREFIX', 'admin/reava-pay'),
    'admin_middleware' => ['web', 'auth', 'admin'],
    'admin_layout' => env('REAVA_PAY_ADMIN_LAYOUT', 'layouts.AdminLayout'),

    // Payment channels
    'channels' => [
        'mpesa' => ['name' => 'M-Pesa', 'icon' => 'fas fa-mobile-alt', 'color' => '#4CAF50', 'enabled' => true],
        'card' => ['name' => 'Card Payment', 'icon' => 'fas fa-credit-card', 'color' => '#2196F3', 'enabled' => true],
        'bank_transfer' => ['name' => 'Bank Transfer', 'icon' => 'fas fa-university', 'color' => '#FF9800', 'enabled' => true],
    ],

    'currencies' => ['KES', 'UGX', 'TZS', 'NGN', 'GHS', 'ZAR', 'USD'],
    'default_currency' => env('REAVA_PAY_CURRENCY', 'KES'),
];
