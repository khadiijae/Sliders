<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/categories/random', [App\http\Controllers\CategoryController::class, 'fetchRandomCategories']);
Route::get('/products/random', [App\http\Controllers\ProductController::class, 'randomProducts']);
Route::get('/products/{id}', [App\http\Controllers\ProductController::class, 'show']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
