<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mailjet Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'API Mailjet
    |
    */

    'api_key_public' => env('MAILJET_APIKEY_PUBLIC', '63f92592baf083fb4b37043e9c16c1b3'),
    'api_key_private' => env('MAILJET_APIKEY_PRIVATE', 'c6dfe57a01fd28090c54a719dc2ff644'),
    'sender_email' => env('MAILJET_SENDER_EMAIL', 'disbonjour2000@gmail.com'),
    'sender_name' => env('MAILJET_SENDER_NAME', 'MOYOO fleet'),

    /*
    |--------------------------------------------------------------------------
    | Configuration API
    |--------------------------------------------------------------------------
    */

    'api_url' => env('MAILJET_API_URL', 'https://api.mailjet.com/v3.1/send'),
    'timeout' => env('MAILJET_TIMEOUT', 30),
    'verify_ssl' => env('MAILJET_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | Configuration par défaut
    |--------------------------------------------------------------------------
    */

    'default_from' => [
        'email' => env('MAILJET_SENDER_EMAIL', 'disbonjour2000@gmail.com'),
        'name' => env('MAILJET_SENDER_NAME', 'MOYOO fleet'),
    ],

    'default_reply_to' => [
        'email' => env('MAILJET_REPLY_TO_EMAIL', 'support@moyoo.com'),
        'name' => env('MAILJET_REPLY_TO_NAME', 'Support MOYOO'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Templates d'emails
    |--------------------------------------------------------------------------
    */

    'templates' => [
        'welcome' => [
            'subject' => 'Bienvenue sur MOYOO fleet',
            'from_name' => 'MOYOO fleet',
            'template_id' => env('MAILJET_WELCOME_TEMPLATE_ID', null),
        ],
        'password_reset' => [
            'subject' => 'Réinitialisation de votre mot de passe - MOYOO fleet',
            'from_name' => 'MOYOO fleet',
            'template_id' => env('MAILJET_PASSWORD_RESET_TEMPLATE_ID', null),
        ],
        'notification' => [
            'subject' => 'Notification MOYOO fleet',
            'from_name' => 'MOYOO fleet',
            'template_id' => env('MAILJET_NOTIFICATION_TEMPLATE_ID', null),
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration des logs
    |--------------------------------------------------------------------------
    */

    'logging' => [
        'enabled' => env('MAILJET_LOGGING_ENABLED', true),
        'log_success' => env('MAILJET_LOG_SUCCESS', true),
        'log_errors' => env('MAILJET_LOG_ERRORS', true),
        'log_channel' => env('MAILJET_LOG_CHANNEL', 'stack'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration de sécurité
    |--------------------------------------------------------------------------
    */

    'security' => [
        'rate_limit' => [
            'enabled' => env('MAILJET_RATE_LIMIT_ENABLED', true),
            'max_attempts' => env('MAILJET_RATE_LIMIT_MAX_ATTEMPTS', 10),
            'decay_minutes' => env('MAILJET_RATE_LIMIT_DECAY_MINUTES', 60),
        ],
        'token_expiry' => env('MAILJET_TOKEN_EXPIRY_MINUTES', 60),
    ]
];
