<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgetPasswordController;
use App\Http\Controllers\ProjectBlackListController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SSOUserController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return to_route('users.index');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Password Reset Routes
    Route::prefix('password')->group(function () {
        Route::get('/forgot', [ForgetPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
        Route::post('/forgot', [ForgetPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
        Route::get('/reset/{token}', [ForgetPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
        Route::post('/reset', [ForgetPasswordController::class, 'resetPassword'])->name('password.update');
    });
    Route::middleware('auth')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::post('/', [UserController::class, 'bulkUpdate'])->name('users.bulk');
            Route::get('/{id}', [UserController::class, 'show'])->name('users.show');
            Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
        });

        Route::prefix('project-black-lists')->group(function () {
            Route::get('/', [ProjectBlackListController::class, 'index'])->name('project-black-lists.index');
            Route::get('/create', [ProjectBlackListController::class, 'create'])->name('project-black-lists.create');
            Route::post('/', [ProjectBlackListController::class, 'store'])->name('project-black-lists.store');
            Route::get('/{id}', [ProjectBlackListController::class, 'show'])->name('project-black-lists.show');
            Route::get('/{id}/edit', [ProjectBlackListController::class, 'edit'])->name('project-black-lists.edit');
            Route::put('/{id}', [ProjectBlackListController::class, 'update'])->name('project-black-lists.update');
            Route::delete('/{id}', [ProjectBlackListController::class, 'destroy'])->name('project-black-lists.destroy');
        });

        Route::prefix('sso-users')->group(function () {
            Route::get('/', [SSOUserController::class, 'index'])->name('sso-users.index');
            Route::post('/', [SSOUserController::class, 'store'])->name('sso-users.store');
            Route::delete('/{id}', [SSOUserController::class, 'destroy'])->name('sso-users.destroy');
        });

        Route::prefix('apps')->group(function () {
            Route::get('/', [ProjectController::class, 'index'])->name('projects.index');
            Route::get('/create', [ProjectController::class, 'create'])->name('projects.create');
            Route::post('/', [ProjectController::class, 'store'])->name('projects.store');
            Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
        });

    });
});
