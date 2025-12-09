<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Single Sign-On system
    |
    */

    'issuer' => env('SSO_ISSUER', 'https://sso.example.com'),

    'clients' => [
        'rashi' => [
            'client_id' => env('RASHI_CLIENT_ID'),
            'client_secret' => env('RASHI_CLIENT_SECRET'),
            'redirect_uri' => env('RASHI_REDIRECT_URI', 'https://rashi.example.com/callback'),
            'scopes' => ['openid', 'profile', 'email'],
        ],
        'visits' => [
            'client_id' => env('VISITS_CLIENT_ID'),
            'client_secret' => env('VISITS_CLIENT_SECRET'),
            'redirect_uri' => env('VISITS_REDIRECT_URI', 'https://visits.example.com/callback'),
            'scopes' => ['openid', 'profile', 'email'],
        ],
        'eftkad' => [
            'client_id' => env('EFTKAD_CLIENT_ID'),
            'client_secret' => env('EFTKAD_CLIENT_SECRET'),
            'redirect_uri' => env('EFTKAD_REDIRECT_URI', 'https://eftkad.example.com/callback'),
            'scopes' => ['openid', 'profile', 'email'],
        ],
    ],

    'oidc' => [
        'enabled' => true,
        'jwks_uri' => '/.well-known/jwks.json',
        'userinfo_endpoint' => '/oauth/userinfo',
        'supported_scopes' => ['openid', 'profile', 'email', 'offline_access'],
        'supported_response_types' => ['code', 'token', 'id_token'],
        'supported_grant_types' => ['authorization_code', 'refresh_token'],
    ],

    'security' => [
        'enforce_https' => env('SSO_ENFORCE_HTTPS', true),
        'rate_limiting' => [
            'authorize' => '60,1', // 60 requests per minute
            'token' => '120,1',
            'userinfo' => '300,1',
        ],
        'csrf_protection' => true,
        'session_lifetime' => 7200, // 2 hours
    ],

    'mfa' => [
        'enabled' => env('SSO_MFA_ENABLED', false),
        'issuer' => env('SSO_MFA_ISSUER', 'SSO Server'),
        'digits' => 6,
        'period' => 30,
    ],
];
