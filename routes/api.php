<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\Tenant\ProductController;
use App\Http\Controllers\Api\Tenant\CategoryController;
use App\Http\Controllers\Api\Tenant\LocationController;
use App\Http\Controllers\Api\Tenant\OrderController;
use App\Http\Controllers\Api\Tenant\PaymentController;
use App\Http\Controllers\Api\Tenant\WebhookController;
use App\Http\Controllers\Api\Tenant\DashboardController;
use App\Http\Controllers\Api\Tenant\InventoryController;




Route::get('/ping', function() {
    return response()->json(['message' => 'pong']);
});

Route::middleware(['api-public'])->prefix('kiosk/{tenant_slug}')->group(function () {
    Route::get('/users', function() {
        return response()->json(['tenant' => app('currentTenant')->id]);
    });
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
        Route::post('/bulk',       [ProductController::class, 'bulkUpload']);   // bulk create
        Route::get('/{id}',    [ProductController::class, 'show']);    // read one
        Route::put('/{product}',   [ProductController::class, 'update']);  // update
        Route::delete('/{product}',[ProductController::class, 'destroy']); // delete

        // Inventory & Movement
        Route::post('/{product}/inventory/adjust', [ProductController::class, 'adjustInventory']);
        Route::post('/{product}/inventory/move',   [ProductController::class, 'moveStock']);

        // Image Upload
        Route::post('/images/bulk',       [ProductController::class, 'bulkImageUpload']);   // bulk image upload
    });

    // Location Management
    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index']);       // List locations
        Route::post('/', [LocationController::class, 'store']);      // Create location
        Route::get('/{id}', [LocationController::class, 'show']);    // Get location details
        Route::put('/{id}', [LocationController::class, 'update']);  // Update location
        Route::delete('/{id}', [LocationController::class, 'destroy']); // Delete location
    });

    // pos orders
    // Route::prefix('orders')->group(function(){
    //     Route::post('/', [OrderController::class,'create']);
    //     Route::put('{order_no}', [OrderController::class,'update']);
    //     Route::post('{order_no}/payment-init', [PaymentController::class,'initiate']);
    //     Route::post('webhook', [WebhookController::class,'handle']);
    // });

    // 1️⃣ Create Draft
    Route::post('/orders', [OrderController::class, 'create']);

    // 2️⃣ Sync Items (Recommended instead of add one by one)
    Route::put('/orders/{order}/items', [OrderController::class, 'updateItems'])->whereNumber('order');

    // If you still want single item endpoint
    // Route::post('/orders/{order}/items', [OrderController::class, 'addItem']);

    // Attach Customer
    Route::patch('/orders/{order}/customer', [OrderController::class, 'attachCustomer']);

    // 3️⃣ Move To Pending Payment
    Route::post('/orders/{order}/pending-payment', [OrderController::class, 'moveToPayment']);

    // 3️⃣ Complete Payment
    Route::post('/orders/{order}/payments', [PaymentController::class, 'createPayment']);

    Route::get('/payments/methods', [PaymentController::class, 'list']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/inventory', [InventoryController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    // 5️⃣ Payment Success Callback
    Route::post('/payments/{payment}/success', [PaymentController::class, 'markSuccess']);

    // 6️⃣ Final Complete (manual completion if needed)
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete']);

    Route::get('/orders/list', [OrderController::class, 'index']);
    Route::get('/orders/kitchen', [OrderController::class, 'kitchenIndex']);

    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);



    Route::prefix('tokens')->group(function () {
        Route::get('{token}', [TokenController::class, 'show']);
        Route::post('{token}/status', [TokenController::class, 'updateStatus']);
    });

    Route::get('/kitchen/orders', [KitchenController::class, 'index']);

});

Route::middleware(['api-protected-untenant'])->prefix('{tenant_slug}')->group(function () {
    Route::post('/open/logout', [AuthController::class, 'logout']);
    Route::get('/open/me', [AuthController::class, 'me']);
});

Route::middleware('apikey')->post('/invoice/generate',[InvoiceController::class,'generate']);
Route::get('/invoice/{uuid}',[InvoiceController::class,'view']);

