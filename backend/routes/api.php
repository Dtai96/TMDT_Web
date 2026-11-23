<?php

use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [StoreController::class, 'products'])->name('products.index');
Route::post('/checkout', [StoreController::class, 'checkout'])->name('checkout');
Route::post('/wholesale', [StoreController::class, 'wholesale'])->name('wholesale');
Route::post('/complaints', [StoreController::class, 'complaint'])->name('complaints.store');
