<?php

use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CountryController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{slug}', [CountryController::class, 'show']);

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1');
