<?php

return [
    'issuer' => env('MFA_ISSUER', 'Avarewase'),
    'window' => 1,
    'digits' => 6,

    // When true, every user must have two-factor authentication enabled to
    // access their account or any protected resource — those who haven't
    // set it up yet are confined to the MFA setup page until they do.
    'required' => env('MFA_REQUIRED', true),

    // Membership codes exempt from the requirement above (comma-separated in
    // MFA_EXEMPT_CODES). Defaults to the app-review account only; accounts that
    // enable MFA themselves are still challenged at login regardless.
    'exempt_codes' => array_values(array_filter(array_map('trim', explode(',', (string) env('MFA_EXEMPT_CODES', 'E1C1F1NR10'))))),
];
