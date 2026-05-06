<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminSupportTicketController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('logout', [AdminAuthController::class, 'destroy'])->name('logout');

        Route::get('/', AdminDashboardController::class)->name('dashboard');

        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('support-tickets', [AdminSupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('support-tickets/{ticket}', [AdminSupportTicketController::class, 'show'])->name('tickets.show');
        Route::put('support-tickets/{ticket}', [AdminSupportTicketController::class, 'update'])->name('tickets.update');
    });
});
