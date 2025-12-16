<?php

use App\Http\Controllers\Api\ForgetPasswordController;
use App\Http\Controllers\Api\SSOController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum', 'check.secret.key'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['check.secret.key'])->group(function () {
    Route::post('/login', [SSOController::class, 'login']);
    Route::post('/qr-login', [SSOController::class, 'Qrlogin']);
    Route::get('/me', [SSOController::class, 'me']);
    Route::post('reset-password-link', [ForgetPasswordController::class, 'sendResetLinkEmail']);
});
