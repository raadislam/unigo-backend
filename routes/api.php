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
