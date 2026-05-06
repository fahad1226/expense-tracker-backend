<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// public routes
Route::post('/auth/login', [UserController::class, 'login']);
Route::post('/auth/register', [UserController::class, 'register']);
Route::get('/auth/user', [UserController::class, 'user']);

// protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [ExpenseController::class, 'dashboard']);
    Route::get('/analytics', [ExpenseController::class, 'analytics']);
    // Route::get('/monthly-expenses', [ExpenseController::class, 'monthlyExpenses']);
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
    Route::put('/settings/password', [SettingsController::class, 'updatePassword']);

    //logout

    Route::post('/auth/logout', [UserController::class, 'logout']);
});
