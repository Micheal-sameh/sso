<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Avarewase SSO API',
    description: 'Member-facing API consumed by the first-party "Avarewase Passport" mobile app: authentication, profile, security (MFA), connected apps, the home dashboard, CMC posts, and notifications.',
)]
#[OA\Server(url: '/', description: 'Current host')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Passport access token',
    description: "Token returned by POST /api/auth/login (or /api/auth/mfa/verify). Send as 'Authorization: Bearer {token}'.",
)]
abstract class Controller
{
    //
}
