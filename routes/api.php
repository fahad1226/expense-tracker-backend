<?php

use App\Http\Controllers\Api\SupportContactController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// The 'throttle:30,1' middleware limits this endpoint to 30 requests per minute per IP (rate limiter).
Route::post('/support/contact', [SupportContactController::class, 'store'])->middleware('throttle:30,1');

// public routes
Route::post('/auth/login', [UserController::class, 'login']);
Route::post('/auth/register', [UserController::class, 'register']);

Route::post('/auth/google', GoogleAuthController::class)->middleware('throttle:20,1');

// protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [UserController::class, 'user']);
    Route::get('/dashboard', [ExpenseController::class, 'dashboard']);
    Route::get('/analytics', [ExpenseController::class, 'analytics']);

    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::put('/expenses/{id}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::get('/reports', [ReportController::class, 'summary']);
    Route::get('/reports/export', [ReportController::class, 'export']);

    Route::get('/budgets', [BudgetController::class, 'show']);
    Route::put('/budgets', [BudgetController::class, 'upsert']);

    Route::get('/settings', [SettingsController::class, 'show']);
    Route::patch('/settings', [SettingsController::class, 'update']);
    Route::post('/settings/avatar', [SettingsController::class, 'uploadAvatar']);
    Route::delete('/settings/avatar', [SettingsController::class, 'destroyAvatar']);
    Route::put('/settings/password', [SettingsController::class, 'updatePassword']);

    Route::post('/auth/logout', [UserController::class, 'logout']);
});
