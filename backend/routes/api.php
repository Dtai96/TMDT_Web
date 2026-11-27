<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\RequireRole;
use Illuminate\Support\Facades\Route;

Route::get('/products', [StoreController::class, 'products'])->name('products.index');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::middleware(AuthenticateApiToken::class)->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/rewards', [StoreController::class, 'rewards']);
    Route::post('/checkout/preview', [StoreController::class, 'preview']);
    Route::post('/checkout', [StoreController::class, 'checkout'])->name('checkout');
    Route::middleware(RequireRole::class.':admin,manager')->prefix('management')->group(function () {
        Route::get('/products', [ManagementController::class, 'products']);
        Route::post('/products', [ManagementController::class, 'storeProduct']);
        Route::put('/products/{product}', [ManagementController::class, 'updateProduct']);
        Route::delete('/products/{product}', [ManagementController::class, 'hideProduct']);
    });
    Route::middleware(RequireRole::class.':admin')->prefix('management')->group(function () {
        Route::get('/users', [ManagementController::class, 'users']);
        Route::put('/users/{user}', [ManagementController::class, 'updateUser']);
        Route::get('/orders', [ManagementController::class, 'orders']);
        Route::put('/orders/{order}', [ManagementController::class, 'updateOrder']);
        Route::get('/complaints', [ManagementController::class, 'complaints']);
        Route::put('/complaints/{complaint}', [ManagementController::class, 'updateComplaint']);
    });
});
Route::post('/complaints', [StoreController::class, 'complaint'])->name('complaints.store');
