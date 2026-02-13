<?php

return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'team_members'),
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'team_members',
        ],
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'team_members',
        ],
    ],

    'providers' => [
        'team_members' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\TeamMember::class),
        ],
    ],

    'passwords' => [
        'team_members' => [
            'provider' => 'team_members',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
