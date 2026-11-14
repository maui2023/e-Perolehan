<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AffanPay Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for AffanPay Payment Gateway (maui2023/affanpay-laravel)
    | Supports sandbox and live environments with webhook signature verification.
    |
    */

    'environment' => env('AFFANPAY_ENVIRONMENT', 'sandbox'),

    'webhook_secret' => env('AFFANPAY_WEBHOOK_SECRET', ''),

    'sandbox' => [
        'url' => env('AFFANPAY_SANDBOX_URL', 'https://sandbox.affanpay.my'),
        'email' => env('AFFANPAY_SANDBOX_EMAIL', ''),
        'password' => env('AFFANPAY_SANDBOX_PASSWORD', ''),
    ],

    'live' => [
        'url' => env('AFFANPAY_LIVE_URL', 'https://app.affanpay.my'),
        'email' => env('AFFANPAY_LIVE_EMAIL', ''),
        'password' => env('AFFANPAY_LIVE_PASSWORD', ''),
    ],

    // Fallback simulation mode for automated testing without live network calls
    'simulation_mode' => env('AFFANPAY_SIMULATION_MODE', false),
];
