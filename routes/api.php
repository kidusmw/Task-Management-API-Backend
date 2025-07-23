<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;


Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('products', ProductController::class);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
});

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);