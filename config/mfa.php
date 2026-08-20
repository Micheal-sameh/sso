<?php

return [
    'issuer' => env('MFA_ISSUER', 'Avarewase'),
    'window' => 1,
    'digits' => 6,

    // When true, every user must have two-factor authentication enabled to
    // access their account or any protected resource — those who haven't
    // set it up yet are confined to the MFA setup page until they do.
    'required' => env('MFA_REQUIRED', true),
];
