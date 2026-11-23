<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'app' => 'Trại Gà Siêu Môn API',
    'frontend' => 'Run the Expo app from the sibling frontend directory.',
    'products' => '/api/products',
]));
