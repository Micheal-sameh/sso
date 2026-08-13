<?php

use App\Http\Controllers\Account\ConnectedAppsController;
use App\Http\Controllers\Account\MfaSettingsController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\OAuth\AuthorizationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;

Route::get('/', fn () => redirect()->route(Auth::check() ? 'account.show' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::middleware('mfa.pending')->group(function () {
        Route::get('/mfa', [MfaController::class, 'showMfa'])->name('mfa.show');
        Route::post('/mfa', [MfaController::class, 'verify'])->name('mfa.verify');
    });

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::post('/', [ProfileController::class, 'update'])->name('update');
        Route::get('/mfa', [MfaSettingsController::class, 'show'])->name('mfa');
        Route::post('/mfa/enable', [MfaSettingsController::class, 'enable'])->name('mfa.enable');
        Route::post('/mfa/confirm', [MfaSettingsController::class, 'confirmEnable'])->name('mfa.confirm');
        Route::post('/mfa/disable', [MfaSettingsController::class, 'disable'])->name('mfa.disable');
        Route::get('/apps', [ConnectedAppsController::class, 'index'])->name('apps');
        Route::delete('/apps/{tokenId}', [ConnectedAppsController::class, 'revoke'])->name('apps.revoke');
    });

    Route::middleware('superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::patch('/users/{user}/toggle-admin-access', [UserController::class, 'toggleAdminAccess'])->name('users.toggle-admin-access');

        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');

        Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
        Route::delete('/tokens/{token}', [TokenController::class, 'revoke'])->name('tokens.revoke');
    });
});

Route::middleware('web')->group(function () {
    Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->name('oauth.authorize');
    Route::post('/oauth/authorize', [ApproveAuthorizationController::class, 'approve'])->name('oauth.authorize.approve');
    Route::delete('/oauth/authorize', [DenyAuthorizationController::class, 'deny'])->name('oauth.authorize.deny');
});
