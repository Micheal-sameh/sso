<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the SSO (OAuth2/OIDC) client implementation.
    |
    */

    'issuer' => env('SSO_ISSUER', 'https://auth.mycompany.com'),

    'client_id' => env('SSO_CLIENT_ID', 'PROJECT_CLIENT_ID'),

    'client_secret' => env('SSO_CLIENT_SECRET', null), // null for public clients

    'redirect_uri' => env('SSO_REDIRECT_URI', 'https://project1.com/auth/callback'),

    'scopes' => ['openid', 'profile', 'email'],

    'endpoints' => [
        'authorize' => env('SSO_ISSUER', 'https://auth.mycompany.com').'/authorize',
        'token' => env('SSO_ISSUER', 'https://auth.mycompany.com').'/token',
        'userinfo' => env('SSO_ISSUER', 'https://auth.mycompany.com').'/userinfo',
        'logout' => env('SSO_ISSUER', 'https://auth.mycompany.com').'/logout',
        'jwks' => env('SSO_ISSUER', 'https://auth.mycompany.com').'/.well-known/jwks.json',
        'revoke' => env('SSO_ISSUER', 'https://auth.mycompany.com').'/revoke', // optional
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Keys
    |--------------------------------------------------------------------------
    |
    | Keys used to store OAuth2 tokens and PKCE data in Laravel session.
    |
    */

    'session_keys' => [
        'state' => 'sso_state',
        'code_verifier' => 'sso_code_verifier',
        'user_id' => 'sso_user_id',
        'id_token' => 'sso_id_token',
        'access_token' => 'sso_access_token',
        'refresh_token' => 'sso_refresh_token',
        'token_expires_at' => 'sso_token_expires_at',
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Refresh
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic token refresh.
    |
    */

    'token_refresh' => [
        'enabled' => true,
        'buffer_time' => 300, // seconds before expiry to refresh
    ],

    /*
    |--------------------------------------------------------------------------
    | User Mapping
    |--------------------------------------------------------------------------
    |
    | Mapping of ID Token claims to User model attributes.
    |
    */

    'user_mapping' => [
        'sub' => 'sub', // unique identifier
        'email' => 'email',
        'name' => 'name',
        'membership_code' => 'membership_code',
    ],
];
