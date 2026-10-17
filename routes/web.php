<?php

use App\Http\Controllers\Account\ConnectedAppsController;
use App\Http\Controllers\Account\MfaSettingsController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Admin\BirthdayController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\FamilyController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\StageController;
use App\Http\Controllers\Admin\TokenController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AvatarUploadController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OAuth\ApproveAuthorizationController;
use App\Http\Controllers\OAuth\AuthorizationController;
use App\Http\Controllers\OAuth\DenyAuthorizationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('account.show');
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
Route::delete('/avatar-upload/{token}', [AvatarUploadController::class, 'destroy'])->name('avatar-upload.destroy');

Route::get('/reset-password/link/{token}', [ResetPasswordController::class, 'showLink'])->name('password.reset.link');
Route::post('/reset-password/link/{token}', [ResetPasswordController::class, 'resetLink'])->name('password.reset.link.update');

Route::middleware('mfa.pending')->group(function () {
    Route::get('/mfa', [MfaController::class, 'showMfa'])->name('mfa.show');
    Route::post('/mfa', [MfaController::class, 'verify'])->name('mfa.verify');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    // Plain "user" accounts exist only to authenticate into other apps via
    // OAuth (oauth/authorize sits in its own middleware group below) — they
    // have no business in this app's own dashboard, so everything past this
    // point requires an admin/secretary/superadmin role.
    //
    // MFA setup is the exception: when MFA is required, a plain user coming
    // from another app's OAuth flow is sent here to enrol, so it must stay
    // reachable without the dashboard role (and then send them back).
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/mfa', [MfaSettingsController::class, 'show'])->name('mfa');
        Route::post('/mfa/enable', [MfaSettingsController::class, 'enable'])->name('mfa.enable');
        Route::post('/mfa/confirm', [MfaSettingsController::class, 'confirmEnable'])->name('mfa.confirm');
        Route::post('/mfa/disable', [MfaSettingsController::class, 'disable'])->name('mfa.disable');
    });

    Route::middleware('dashboard')->group(function () {
        Route::middleware('mfa.required')->group(function () {
            Route::prefix('account')->name('account.')->group(function () {
                Route::get('/', [ProfileController::class, 'show'])->name('show');
                Route::post('/password', [PasswordController::class, 'update'])->name('password.update');
                Route::get('/apps', [ConnectedAppsController::class, 'index'])->name('apps');
                Route::delete('/apps/{tokenId}', [ConnectedAppsController::class, 'revoke'])->name('apps.revoke');
            });

            Route::get('/more', fn () => Inertia::render('More/Index'))->name('more.index');
            Route::get('/liturgy', fn () => Inertia::render('Liturgy/Index'))->name('liturgy.index');
            Route::get('/liturgy/agpeya', fn () => Inertia::render('Liturgy/Agpeya/Index'))->name('liturgy.agpeya.index');

            Route::middleware('superadmin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/birthdays', [BirthdayController::class, 'index'])->name('birthdays.index');

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
            Route::delete('/users/{user}/avatar', [UserController::class, 'removeAvatar'])->name('users.avatar.destroy');
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

            Route::get('/families', [FamilyController::class, 'index'])->name('families.index');
            Route::get('/families/{family}', [FamilyController::class, 'show'])->name('families.show');
            Route::post('/families', [FamilyController::class, 'store'])->name('families.store');
            Route::delete('/families/{family}', [FamilyController::class, 'destroy'])->name('families.destroy');
            Route::post('/families/{family}/members', [FamilyController::class, 'addMember'])->name('families.members.add');
            Route::delete('/families/{family}/members/{user}', [FamilyController::class, 'removeMember'])->name('families.members.remove');
            Route::post('/families/require-data-update', [FamilyController::class, 'requireDataUpdateAll'])->name('families.require-data-update.all');
            Route::post('/families/{family}/require-data-update', [FamilyController::class, 'requireDataUpdateForFamily'])->name('families.require-data-update.one');
            });
        });
    });
});

Route::middleware(['web', 'mfa.required'])->group(function () {
    Route::get('/oauth/authorize', [AuthorizationController::class, 'authorize'])->name('oauth.authorize');
    Route::post('/oauth/authorize', [ApproveAuthorizationController::class, 'approve'])->name('oauth.authorize.approve');
    Route::delete('/oauth/authorize', [DenyAuthorizationController::class, 'deny'])->name('oauth.authorize.deny');
});
