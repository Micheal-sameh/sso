<?php

use App\Http\Controllers\OAuth\UserinfoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'scopes:openid'])->get('/userinfo', [UserinfoController::class, 'me']);
