<?php

use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin-api')->group(function () {
    Route::get('/csrf', [AdminAuthController::class, 'csrf']);
    Route::post('/auth/login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(EnsureAdminAuthenticated::class)->group(function () {
        Route::get('/auth/me', [AdminAuthController::class, 'me']);
        Route::post('/auth/logout', [AdminAuthController::class, 'logout']);
    });
});
