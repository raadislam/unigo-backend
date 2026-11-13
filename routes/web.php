<?php

use App\Http\Controllers\Api\Admin\AdminAuthController;
use App\Http\Controllers\Api\Admin\VisaRequirementManagementController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsureAdminRole;
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

        Route::prefix('visa-requirements')->group(function () {
            Route::get('/countries', [VisaRequirementManagementController::class, 'countries']);
            Route::get('/', [VisaRequirementManagementController::class, 'index']);
            Route::get('/{id}', [VisaRequirementManagementController::class, 'show'])->whereNumber('id');

            Route::middleware(EnsureAdminRole::class.':super_admin,admin,editor')->group(function () {
                Route::post('/', [VisaRequirementManagementController::class, 'store']);
                Route::put('/{id}', [VisaRequirementManagementController::class, 'update'])->whereNumber('id');
                Route::post('/{id}/sources', [VisaRequirementManagementController::class, 'addSource'])->whereNumber('id');
                Route::delete('/{id}/sources/{sourceId}', [VisaRequirementManagementController::class, 'deleteSource'])->whereNumber('id')->whereNumber('sourceId');
                Route::post('/{id}/verification', [VisaRequirementManagementController::class, 'verify'])->whereNumber('id');
                Route::post('/{id}/publish', [VisaRequirementManagementController::class, 'publish'])->whereNumber('id');
                Route::post('/{id}/unpublish', [VisaRequirementManagementController::class, 'unpublish'])->whereNumber('id');
            });
        });
    });
});
