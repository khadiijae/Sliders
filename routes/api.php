<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SliderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('/categories', App\http\Controllers\CategoryController::class);
Route::post('/categories/{id}', [CategoryController::class, 'update']);

Route::apiResource('/products', App\http\Controllers\ProductController::class);
Route::apiResource('/vendors', App\http\Controllers\VendorController::class);

Route::apiResource('/sliders', App\http\Controllers\SliderController::class);
Route::post('/sliders/{id}', [SliderController::class, 'update']);
