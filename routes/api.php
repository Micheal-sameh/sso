<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CmcPostController;
use App\Http\Controllers\Api\ConnectedAppsController;
use App\Http\Controllers\Api\DonationCallbackController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SecurityController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\YoutubeWebhookController;
use App\Http\Controllers\OAuth\OdooTokenValidationController;
use App\Http\Controllers\OAuth\UserinfoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'scopes:openid'])->get('/userinfo', [UserinfoController::class, 'me']);

// Odoo's auth_oauth module calls its "Validation Endpoint" with the access
// token as a query parameter, not an Authorization header — see the
// controller for details. Throttled since it's an unauthenticated GET.
Route::middleware('throttle:per-second')->get('/oauth/odoo/validate', [OdooTokenValidationController::class, 'validate']);

// Server-to-server: registered OAuth clients authenticate with client_id/client_secret
// (no bearer token) to request a password reset email be sent to one of their users.
Route::post('/password/forgot', [PasswordResetController::class, 'forgot']);

// First-party mobile app ("Avarewase Passport"): direct membership_code/password
// login against this SSO's own API, no OAuth consent screen involved.
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/mfa/verify', [AuthController::class, 'verifyMfa']);

// Server-to-server: bank gateway payment notifications. Not session/token
// authenticated — DonationService verifies each request's signature against
// the gateway's webhook secret before trusting it.
Route::post('/donations/callback/{gateway}', [DonationCallbackController::class, 'handle'])->middleware('throttle:per-second');

// YouTube WebSub (PubSubHubbub) callback: the hub GETs this URL to verify a
// subscription and POSTs an Atom feed here on every new upload.
Route::middleware('throttle:per-second')->get('/webhooks/youtube', [YoutubeWebhookController::class, 'verify'])->name('webhooks.youtube');
Route::middleware('throttle:per-second')->post('/webhooks/youtube', [YoutubeWebhookController::class, 'handle']);

Route::middleware('auth:api')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/security', [SecurityController::class, 'show']);
    Route::post('/security/mfa/enable', [SecurityController::class, 'mfaEnable']);
    Route::post('/security/mfa/confirm', [SecurityController::class, 'mfaConfirm']);
    Route::post('/security/mfa/disable', [SecurityController::class, 'mfaDisable']);

    Route::middleware('mfa.required')->group(function () {
        Route::get('/me', [ProfileController::class, 'me']);
        Route::post('/security/password', [SecurityController::class, 'updatePassword']);
        Route::get('/connected-apps', [ConnectedAppsController::class, 'index']);
        Route::delete('/connected-apps/{tokenId}', [ConnectedAppsController::class, 'revoke']);

        Route::get('/home', [HomeController::class, 'index']);

        Route::get('/cmc-posts', [CmcPostController::class, 'index']);
        Route::get('/cmc-posts/{cmcPost}', [CmcPostController::class, 'show']);

        Route::get('/videos', [VideoController::class, 'index']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('/notifications/read-many', [NotificationController::class, 'markManyRead']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    });
});
