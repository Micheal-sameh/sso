<?php

use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\OAuth\UserinfoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'scopes:openid'])->get('/userinfo', [UserinfoController::class, 'me']);

// Server-to-server: registered OAuth clients authenticate with client_id/client_secret
// (no bearer token) to request a password reset email be sent to one of their users.
Route::post('/password/forgot', [PasswordResetController::class, 'forgot']);
