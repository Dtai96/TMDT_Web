<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

Route::get('/products', [StoreController::class, 'products'])->name('products.index');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::middleware(AuthenticateApiToken::class)->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/checkout', [StoreController::class, 'checkout'])->name('checkout');
});
Route::post('/wholesale', [StoreController::class, 'wholesale'])->name('wholesale');
Route::post('/complaints', [StoreController::class, 'complaint'])->name('complaints.store');
