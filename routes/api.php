<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TenantController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes with Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Tenant Management - Super Admin only
    Route::prefix('tenants')->group(function () {
        Route::get('/', [TenantController::class, 'index'])->middleware('role:SuperAdmin');
        Route::post('/', [TenantController::class, 'store'])->middleware('role:SuperAdmin');
        Route::get('/{id}/statistics', [TenantController::class, 'statistics'])->middleware('role:SuperAdmin');
        Route::get('/{id}', [TenantController::class, 'show'])->middleware('role:SuperAdmin');
        Route::put('/{id}', [TenantController::class, 'update'])->middleware('role:SuperAdmin');
        Route::delete('/{id}', [TenantController::class, 'destroy'])->middleware('role:SuperAdmin');
        
    });
    
    // User Management - Admin only
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('role:Admin');
        Route::post('/', [UserController::class, 'store'])->middleware('role:Admin');
        Route::put('/{user}', [UserController::class, 'update'])->middleware('role:Admin');
        Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('role:Admin');
    });
    
    // Admin registration (admins can create other users)
    Route::post('/register', [AuthController::class, 'register'])->middleware('role:Admin');
    
    // Expense Management with role-based access
    Route::prefix('expenses')->group(function () {
        // All authenticated users can view and create expenses
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
        
        // Only managers and admins can update
        Route::put('/{expense}', [ExpenseController::class, 'update'])
            ->middleware('role:Admin,Manager');
        
        // Only admins can delete
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])
            ->middleware('role:Admin');
        
        // Summary report (admins and managers)
        Route::get('/report/summary', [ExpenseController::class, 'summary'])
            ->middleware('role:Admin,Manager');
    });
});