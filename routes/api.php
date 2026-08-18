<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ConnectedAppsController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SecurityController;
use App\Http\Controllers\OAuth\OdooTokenValidationController;
use App\Http\Controllers\OAuth\UserinfoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'scopes:openid'])->get('/userinfo', [UserinfoController::class, 'me']);

// Odoo's auth_oauth module calls its "Validation Endpoint" with the access
// token as a query parameter, not an Authorization header — see the
// controller for details. Throttled since it's an unauthenticated GET.
Route::middleware('throttle')->get('/oauth/odoo/validate', [OdooTokenValidationController::class, 'validate']);

// Server-to-server: registered OAuth clients authenticate with client_id/client_secret
// (no bearer token) to request a password reset email be sent to one of their users.
Route::post('/password/forgot', [PasswordResetController::class, 'forgot']);

// First-party mobile app ("Avarewase Passport"): direct membership_code/password
// login against this SSO's own API, no OAuth consent screen involved.
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/mfa/verify', [AuthController::class, 'verifyMfa']);

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/me', [ProfileController::class, 'me']);

    Route::get('/security', [SecurityController::class, 'show']);
    Route::post('/security/mfa/enable', [SecurityController::class, 'mfaEnable']);
    Route::post('/security/mfa/confirm', [SecurityController::class, 'mfaConfirm']);
    Route::post('/security/mfa/disable', [SecurityController::class, 'mfaDisable']);
    Route::post('/security/password', [SecurityController::class, 'updatePassword']);

    Route::get('/connected-apps', [ConnectedAppsController::class, 'index']);
    Route::delete('/connected-apps/{tokenId}', [ConnectedAppsController::class, 'revoke']);
});
