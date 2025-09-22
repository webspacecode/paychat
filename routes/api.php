<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\Tenant\ProductController;
use App\Http\Controllers\Api\Tenant\CategoryController;
use App\Http\Controllers\Api\Tenant\LocationController;




Route::get('/ping', fn () => response()->json(['message' => 'pong']));

Route::middleware(['api-public'])->prefix('{tenant_slug}')->group(function () {
    Route::get('/users', fn () => response()->json(['tenant' => app('currentTenant')->id]));
});

Route::post('/register-tenant', [TenantController::class, 'register']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['api-protected'])->prefix('{tenant_slug}')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Category Management
    Route::prefix('categories')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{category}', [CategoryController::class, 'update']);
        Route::delete('/{category}', [CategoryController::class, 'destroy']);
        Route::get('/search', [CategoryController::class, 'search']);
        Route::get('/{id}', [CategoryController::class, 'show']); 
    });

    // Product Management
    Route::prefix('products')->group(function () {
        // CRUD
        Route::get('/',        [ProductController::class, 'index']);   // search/list
        Route::post('/',       [ProductController::class, 'store']);   // create
        Route::get('/{id}',    [ProductController::class, 'show']);    // read one
        Route::put('/{product}',   [ProductController::class, 'update']);  // update
        Route::delete('/{product}',[ProductController::class, 'destroy']); // delete

        // Inventory & Movement
        Route::post('/{product}/inventory/adjust', [ProductController::class, 'adjustInventory']);
        Route::post('/{product}/inventory/move',   [ProductController::class, 'moveStock']);
    });

    // Location Management
    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index']);       // List locations
        Route::post('/', [LocationController::class, 'store']);      // Create location
        Route::get('/{id}', [LocationController::class, 'show']);    // Get location details
        Route::put('/{id}', [LocationController::class, 'update']);  // Update location
        Route::delete('/{id}', [LocationController::class, 'destroy']); // Delete location
    });
});

Route::middleware(['api-protected-untenant'])->prefix('{tenant_slug}')->group(function () {
    Route::post('/open/logout', [AuthController::class, 'logout']);
    Route::get('/open/me', [AuthController::class, 'me']);
});