<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "nabda", "log"
    |
    */

    'driver' => env('WHATSAPP_DRIVER', 'log'),

    'nabda' => [
        'base_url' => env('NABDA_BASE_URL', 'https://api.nabdaotp.com'),
        'api_key' => env('NABDA_API_KEY'),
        'instance_id' => env('NABDA_INSTANCE_ID'),
        'instance_token' => env('NABDA_INSTANCE_TOKEN'),
    ],

    'otp' => [
        'length' => 6,
        'expiry_minutes' => 10,
        'max_attempts' => 5,
        'resend_cooldown_seconds' => 60,
    ],

];
