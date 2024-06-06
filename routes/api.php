<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductimageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('logout', 'logout');
});

/*
Route::middleware('auth:api')->group(function () {
    // Category routes
    Route::apiResource('/categories', CategoryController::class);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);

    // Product routes
    Route::apiResource('/products', ProductController::class);

    // Product image routes
    Route::apiResource('/productsimage', ProductimageController::class);
    Route::post('/productsimage/{id}', [ProductimageController::class, 'update']);

    // Vendor routes
    // Route::apiResource('/vendors', VendorController::class);

    // Slider routes
    Route::apiResource('/sliders', SliderController::class);
    Route::post('/sliders/{id}', [SliderController::class, 'update']);
});
*/
Route::apiResource('/categories', CategoryController::class);
Route::post('/categories/{id}', [CategoryController::class, 'update']);

// Product routes
Route::apiResource('/products', ProductController::class);
Route::post('/products/{id}', [ProductController::class, 'update']);

// Product image routes
Route::apiResource('/productsimage', ProductimageController::class);
Route::post('/productsimage/{id}', [ProductimageController::class, 'update']);

// Vendor routes
// Route::apiResource('/vendors', VendorController::class);

// Slider routes
Route::apiResource('/sliders', SliderController::class);
Route::post('/sliders/{id}', [SliderController::class, 'update']);

Route::apiResource('/blogs', BlogController::class);
Route::post('/blogs/{id}', [BlogController::class, 'update']);



Route::post('/cart', [CartController::class, 'store']);
Route::post('/getPanierData', [CartController::class, 'getPanierData']);
Route::post('/createOrder', [CartController::class, 'createOrder']);
Route::get('/getOrder/{orderId}', [CartController::class, 'getOrderDetails']);

//Route::delete('/cart/{cartItemId}', [CartController::class, 'delete']);
Route::get('search', [ProductController::class, 'searchQuery']);
Route::get('userDetails', [UserController::class, 'userDetails']);
