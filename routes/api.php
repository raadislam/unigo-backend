<?php

use App\\Http\\Controllers\\Api\\Admin\\VisaRequirementManagementController;
use App\\Http\\Controllers\\Api\\ContactController;
use App\\Http\\Controllers\\Api\\CountryController;
use App\\Http\\Controllers\\Api\\VisaRequirementController;
use App\\Http\\Controllers\\Api\\VisaChecklistPdfController;
use Illuminate\\Support\\Facades\\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/countries/{slug}', [CountryController::class, 'show']);

    Route::get('/visa-requirements', [VisaRequirementController::class, 'index']);
    Route::get('/visa-requirements/{destination}', [VisaRequirementController::class, 'show']);
});

Route::post('/visa-requirements/{destination}/checklist-pdf', VisaChecklistPdfController::class)->middleware('throttle:20,1');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1');

Route::prefix('admin/visa-requirements')->middleware('unigo.admin.signature')->group(function () {
    Route::get('/countries', [VisaRequirementManagementController::class, 'countries']);
    Route::get('/', [VisaRequirementManagementController::class, 'index']);
    Route::post('/', [VisaRequirementManagementController::class, 'store']);
    Route::get('/{id}', [VisaRequirementManagementController::class, 'show'])->whereNumber('id');
    Route::put('/{id}', [VisaRequirementManagementController::class, 'update'])->whereNumber('id');
    Route::post('/{id}/sources', [VisaRequirementManagementController::class, 'addSource'])->whereNumber('id');
    Route::delete('/{id}/sources/{sourceId}', [VisaRequirementManagementController::class, 'deleteSource'])->whereNumber('id')->whereNumber('sourceId');
    Route::post('/{id}/verification', [VisaRequirementManagementController::class, 'verify'])->whereNumber('id');
    Route::post('/{id}/publish', [VisaRequirementManagementController::class, 'publish'])->whereNumber('id');
    Route::post('/{id}/unpublish', [VisaRequirementManagementController::class, 'unpublish'])->whereNumber('id');
});
