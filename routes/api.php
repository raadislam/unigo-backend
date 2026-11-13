<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\VisaRequirementController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{slug}', [CountryController::class, 'show']);

Route::get('/visa-requirements', [VisaRequirementController::class, 'index']);
Route::get('/visa-requirements/{destination}', [VisaRequirementController::class, 'show']);

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1');


Route::prefix('/admin/visa-requirements')->middleware('unigo.admin.signature')->group(function () {
    Route::get('/countries', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'countries']);
    Route::get('/', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'index']);
    Route::post('/', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'store']);
    Route::get('/{id}', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'show'])->whereNumber('id');
    Route::put('/{id}', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'update'])->whereNumber('id');
    Route::post('/{id}/sources', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'addSource'])->whereNumber('id');
    Route::delete('/{id}/sources/{sourceId}', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'deleteSource'])->whereNumber('id')->whereNumber('sourceId');
    Route::post('/{id}/verification', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'verify'])->whereNumber('id');
    Route::post('/{id}/publish', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'publish'])->whereNumber('id');
    Route::post('/{id}/unpublish', [App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController::class, 'unpublish'])->whereNumber('id');
});
