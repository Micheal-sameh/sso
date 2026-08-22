<?php

use App\Http\Controllers\Account\ConnectedAppsController;
use App\Http\Controllers\Account\MfaSettingsController;
use App\Http\Controllers\Account\NotificationController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CmcPostController as AdminCmcPostController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\FundController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\StageController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AvatarUploadController;
use App\Http\Controllers\CmcPostController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OAuth\AuthorizationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('home.index');
    }

    return Inertia::render('Welcome');
})->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/avatar-upload/{token}', [AvatarUploadController::class, 'show'])->name('avatar-upload.show');
Route::post('/avatar-upload/{token}', [AvatarUploadController::class, 'update'])->name('avatar-upload.update');

Route::middleware('mfa.pending')->group(function () {
    Route::get('/mfa', [MfaController::class, 'showMfa'])->name('mfa.show');
    Route::post('/mfa', [MfaController::class, 'verify'])->name('mfa.verify');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/mfa', [MfaSettingsController::class, 'show'])->name('mfa');
        Route::post('/mfa/enable', [MfaSettingsController::class, 'enable'])->name('mfa.enable');
        Route::post('/mfa/confirm', [MfaSettingsController::class, 'confirmEnable'])->name('mfa.confirm');
        Route::post('/mfa/disable', [MfaSettingsController::class, 'disable'])->name('mfa.disable');
    });

    Route::middleware('mfa.required')->group(function () {
        Route::prefix('account')->name('account.')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('show');
            Route::post('/password', [PasswordController::class, 'update'])->name('password.update');
            Route::get('/apps', [ConnectedAppsController::class, 'index'])->name('apps');
            Route::delete('/apps/{tokenId}', [ConnectedAppsController::class, 'revoke'])->name('apps.revoke');
        });

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/read-many', [NotificationController::class, 'markManyRead'])->name('notifications.read-many');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])
            ->middleware('can:view,notification')
            ->name('notifications.read');

        Route::get('/home', [HomeController::class, 'index'])->name('home.index');

        Route::get('/cmc', [CmcPostController::class, 'index'])->name('cmc.index');
        Route::get('/cmc/{cmcPost}', [CmcPostController::class, 'show'])->name('cmc.show');

        Route::prefix('donations')->name('donations.')->group(function () {
            Route::get('/', [DonationController::class, 'create'])->name('create');
            Route::post('/', [DonationController::class, 'store'])->name('store');
            Route::get('/history', [DonationController::class, 'history'])->name('history');
            Route::get('/return', [DonationController::class, 'returnPage'])->name('return');
            Route::get('/sandbox/{donation:uuid}', [DonationController::class, 'sandbox'])->name('sandbox');
            Route::post('/sandbox/{donation:uuid}/pay', [DonationController::class, 'sandboxPay'])->name('sandbox.pay');
        });

        Route::middleware('superadmin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::get('/admins', [UserController::class, 'admins'])->name('admins.index');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
            Route::patch('/users/{user}/toggle-requires-data-update', [UserController::class, 'toggleRequiresDataUpdate'])
                ->name('users.toggle-requires-data-update');
            Route::patch('/users/{user}/toggle-admin-access', [UserController::class, 'toggleAdminAccess'])
                ->middleware('can:manage admin access')
                ->name('users.toggle-admin-access');
            Route::post('/users/{user}/client-access/{client}', [UserController::class, 'grantClientAccess'])
                ->name('users.client-access.grant');
            Route::delete('/users/{user}/client-access/{client}', [UserController::class, 'revokeClientAccess'])
                ->name('users.client-access.revoke');
            Route::post('/users/{user}/reset-link', [UserController::class, 'generateResetLink'])->name('users.reset-link');
            Route::post('/users/{user}/mfa/reset', [UserController::class, 'resetMfa'])->name('users.mfa.reset');
            Route::post('/users/{user}/avatar-upload-link', [UserController::class, 'generateAvatarUploadLink'])
                ->name('users.avatar-upload-link');
            Route::post('/users/{user}/groups/{group}', [UserController::class, 'attachGroup'])->name('users.groups.attach');
            Route::delete('/users/{user}/groups/{group}', [UserController::class, 'detachGroup'])->name('users.groups.detach');

            Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
            Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
            Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
            Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

            Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
            Route::delete('/tokens/{token}', [TokenController::class, 'revoke'])->name('tokens.revoke');

            Route::get('/stages', [StageController::class, 'index'])->name('stages.index');
            Route::post('/stages', [StageController::class, 'store'])->name('stages.store');
            Route::put('/stages/{stage}', [StageController::class, 'update'])->name('stages.update');
            Route::delete('/stages/{stage}', [StageController::class, 'destroy'])->name('stages.destroy');

            Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
            Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
            Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
            Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
            Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');

            Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
            Route::post('/notifications', [AdminNotificationController::class, 'store'])->name('notifications.store');
            Route::delete('/notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('notifications.destroy');

            Route::middleware('can:manage cmc posts')->prefix('cmc-posts')->name('cmc-posts.')->group(function () {
                Route::get('/', [AdminCmcPostController::class, 'index'])->name('index');
                Route::post('/', [AdminCmcPostController::class, 'store'])->name('store');
                Route::put('/{cmcPost}', [AdminCmcPostController::class, 'update'])->name('update');
                Route::delete('/{cmcPost}', [AdminCmcPostController::class, 'destroy'])->name('destroy');
            });

            Route::middleware('can:manage donations')->group(function () {
                Route::prefix('donations')->name('donations.')->group(function () {
                    Route::get('/', [AdminDonationController::class, 'index'])->name('index');
                    Route::get('/export', [AdminDonationController::class, 'export'])->name('export');
                });

                Route::prefix('funds')->name('funds.')->group(function () {
                    Route::get('/', [FundController::class, 'index'])->name('index');
                    Route::post('/', [FundController::class, 'store'])->name('store');
                    Route::put('/{fund}', [FundController::class, 'update'])->name('update');
                    Route::delete('/{fund}', [FundController::class, 'destroy'])->name('destroy');
                });
            });
        });
    });
});

Route::middleware(['web', 'mfa.required'])->group(function () {
    Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->name('oauth.authorize');
    Route::post('/oauth/authorize', [ApproveAuthorizationController::class, 'approve'])->name('oauth.authorize.approve');
    Route::delete('/oauth/authorize', [DenyAuthorizationController::class, 'deny'])->name('oauth.authorize.deny');
});
