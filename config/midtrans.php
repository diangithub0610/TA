<?php

return [
    // Midtrans server key
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    // Midtrans client key
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    // Midtrans environment (true for production, false for sandbox)
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Midtrans 3DS transaction
    'is_3ds' => true,

    // Midtrans sanitization
    'is_sanitized' => true,
];