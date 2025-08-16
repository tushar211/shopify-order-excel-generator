<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/orders', [\App\Http\Controllers\HomeController::class, 'index']);
Route::get('/orders/download', [\App\Http\Controllers\HomeController::class, 'download']);


Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/fetch', [ProductController::class, 'fetchFromShopify'])->name('products.fetch');
