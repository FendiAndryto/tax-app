<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AiAgentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public Auth routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Core endpoints
    Route::prefix('v1')->group(function () {
        // Accounts (Public authenticated read)
        Route::get('/accounts', [AccountController::class, 'index']);

        // Categories (Public authenticated read)
        Route::get('/categories', [CategoryController::class, 'index']);

        // Transactions
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions/{id}', [TransactionController::class, 'show']);
        Route::put('/transactions/{id}', [TransactionController::class, 'update']);
        Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);

        // Admin Only Endpoints (Master Data & User Management)
        Route::middleware('admin')->group(function () {
            // Accounts Management
            Route::post('/accounts', [AccountController::class, 'store']);
            Route::put('/accounts/{id}', [AccountController::class, 'update']);
            Route::patch('/accounts/{id}/toggle-status', [AccountController::class, 'toggleStatus']);
            Route::delete('/accounts/{id}', [AccountController::class, 'destroy']);

            // Categories Management
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::patch('/categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

            // User Management
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users', [UserController::class, 'store']);
            Route::put('/users/{id}', [UserController::class, 'update']);
            Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
            Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        });

        // Financial Metrics Overview
        Route::get('/financial-overview', [TransactionController::class, 'financialOverview']);

        // AI Agent Endpoints
        Route::post('/ai/chat', [AiAgentController::class, 'chat']);
        Route::get('/ai/tools', [AiAgentController::class, 'tools']);
        Route::post('/ai/execute-tool', [AiAgentController::class, 'executeTool']);
    });
});
