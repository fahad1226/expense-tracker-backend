<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;



// public routes
Route::post('/auth/login', [UserController::class, 'login']);
Route::post('/auth/register', [UserController::class, 'register']);
Route::post('/auth/logout', [UserController::class, 'logout']);
Route::get('/auth/user', [UserController::class, 'user']);



// protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{id}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);


    Route::get('/reports', [ExpenseController::class, 'generateReport']);
});
