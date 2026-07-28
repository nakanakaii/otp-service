<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OTP Length
    |--------------------------------------------------------------------------
    |
    | The number of digits in the OTP code. Default is 6.
    |
    */

    'length' => env('OTP_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | The number of minutes until an OTP code expires. Default is 5.
    |
    */

    'expiration_minutes' => env('OTP_EXPIRATION_MINUTES', 5),

    /*
    |--------------------------------------------------------------------------
    | Max Attempts
    |--------------------------------------------------------------------------
    |
    | The maximum number of verification attempts before the OTP is invalidated.
    | Default is 5.
    |
    */

    'max_attempts' => env('OTP_MAX_ATTEMPTS', 5),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Limits how often an OTP can be generated per model instance. Uses
    | Laravel's cache-backed RateLimiter. Disabled by default.
    |
    */

    'rate_limit' => [
        'enabled' => env('OTP_RATE_LIMIT_ENABLED', false),
        'max_attempts' => env('OTP_RATE_LIMIT_MAX', 3),
        'decay_minutes' => env('OTP_RATE_LIMIT_DECAY', 1),
    ],

];
