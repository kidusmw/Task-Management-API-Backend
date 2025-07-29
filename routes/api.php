<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('products', ProductController::class);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::put('/cart/update/{cartItemId}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{cartItemId}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::get('/cart/summary', [CartController::class, 'getCartSummary']);
});

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);