<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('products', ProductController::class);

    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('/tasks', TaskController::class);
    Route::apiResource('/products', ProductController::class);

    // Image Upload Endpoint
    Route::post('/upload', function (Request $request) {
        $request->validate(['image' => 'required|image']);
        
        $path = $request->file('image')->store('public/products');
        $url = Storage::url($path);
        
        return response()->json(['url' => $url]);
    })->middleware('auth:sanctum');
});
});

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);