<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/customers',[CustomerController::class, 'create']);

Route::post('/products',[ProductController::class, 'create']);

Route::get('/products',[ProductController::class, 'index']);

Route::post('/order',[OrderController::class,'store']);

Route::get('/orders',[OrderController::class,'index']);

Route::get('/customers/{id}/orders',[CustomerController::class,'customerOrders']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
