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
use App\Http\Controllers\Api\Tenant\CustomerController;
use App\Http\Controllers\Api\Tenant\DiningStructureController;
use App\Http\Controllers\Api\Tenant\InfoController;
use App\Http\Controllers\Api\Tenant\InlineTokenController;
use App\Http\Controllers\Api\Tenant\KitchenController;
use App\Http\Controllers\Api\Tenant\KitchenBatchController;
use App\Http\Controllers\Api\Tenant\KitchenQueueController;
use App\Http\Controllers\Api\Tenant\TableController;
use App\Http\Controllers\Api\Tenant\TableSessionController;
use App\Http\Controllers\Api\Tenant\PhonePeController;
use App\Http\Controllers\Api\Tenant\TokenController;
use App\Http\Controllers\Api\DemoLeadController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\Tenant\ReportController;
use App\Http\Controllers\Api\Tenant\OfflineOrderSyncController;



Route::post('/demo-leads', [DemoLeadController::class, 'store']);


Route::get('/ping', function() {
    return response()->json(['message' => 'pong']);
});

Route::middleware(['api-public'])->prefix('kiosk/{tenant_slug}')->group(function () {
    Route::get('/users', function() {
        return response()->json(['tenant' => app('currentTenant')->id]);
    });

    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/payments/methods', [PaymentController::class, 'list']);
    Route::get('/categories/search', [CategoryController::class, 'search']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'create']);
    Route::put('/orders/{order}/items', [OrderController::class, 'updateItems'])->whereNumber('order');
    Route::post('/orders/{order}/pending-payment', [OrderController::class, 'moveToPayment']);
    Route::post('/orders/{order}/payments', [PaymentController::class, 'createPayment']);
    Route::patch('/orders/{order}/customer', [OrderController::class, 'attachCustomer'])->whereNumber('order');
    Route::post('/payments/{payment}/success', [PaymentController::class, 'markSuccess']);
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');
});

Route::post('/register-tenant', [TenantController::class, 'register']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['api-protected'])->prefix('{tenant_slug}')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/customers', [CustomerController::class, 'index']);

    // Category Management
    Route::prefix('categories')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::post('/bulk', [CategoryController::class, 'bulkUpload']);
        Route::get('/bulk/template', [CategoryController::class, 'bulkTemplate']);
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

    Route::prefix('tables')->group(function () {
        Route::get('/', [TableController::class, 'index']);
        Route::post('/', [TableController::class, 'store']);
        Route::match(['put', 'patch'], '/{table}', [TableController::class, 'update'])->whereNumber('table');
        Route::patch('/{table}/status', [TableController::class, 'updateStatus'])->whereNumber('table');
        Route::post('/{table}/release', [TableController::class, 'release'])->whereNumber('table');
    });

    Route::prefix('dining-structure')->group(function () {
        Route::get('/', [DiningStructureController::class, 'index']);
        Route::post('/tables/bulk', [DiningStructureController::class, 'bulkUpsert']);
        Route::patch('/tables/{table}/position', [DiningStructureController::class, 'updatePosition'])->whereNumber('table');
    });

    Route::prefix('table-sessions')->group(function () {
        Route::post('/', [TableSessionController::class, 'store']);
        Route::get('/open', [TableSessionController::class, 'open']);
        Route::post('/{session}/close', [TableSessionController::class, 'close'])->whereNumber('session');
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

    Route::patch('/orders/{order}/table', [OrderController::class, 'assignTable'])->whereNumber('order');
    Route::post('/orders/{order}/send-to-kitchen', [OrderController::class, 'sendToKitchen'])->whereNumber('order');
    Route::post('/orders/{order}/inline-token', [InlineTokenController::class, 'store'])->whereNumber('order');

    // Cancel Order
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->whereNumber('order');

    // 3️⃣ Move To Pending Payment
    Route::post('/orders/{order}/pending-payment', [OrderController::class, 'moveToPayment']);

    // 3️⃣ Complete Payment
    Route::post('/orders/{order}/payments', [PaymentController::class, 'createPayment']);

    Route::get('/payments/methods', [PaymentController::class, 'list']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/offline-orders/sync', [OfflineOrderSyncController::class, 'sync']);

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
    Route::get('/orders/{order}/kitchen-batches', [OrderController::class, 'kitchenBatches'])->whereNumber('order');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');



    Route::prefix('tokens')->group(function () {
        Route::get('{token}', [TokenController::class, 'show']);
        Route::post('{token}/status', [TokenController::class, 'updateStatus']);
    });

    Route::get('/kitchen/orders', [KitchenController::class, 'index']);
    Route::get('/kitchen/queue', [KitchenQueueController::class, 'index']);
    Route::patch('/kitchen-batches/{batch}/status', [KitchenBatchController::class, 'updateStatus'])->whereNumber('batch');


    Route::prefix('reports')->group(function () {
        Route::get('/summary', [ReportController::class, 'summary']);
        Route::get('/payments', [ReportController::class, 'payments']);
        Route::get('/top-products', [ReportController::class, 'topProducts']);
        Route::get('/hourly', [ReportController::class, 'hourly']);
    });

});

Route::middleware(['api-protected-untenant'])->prefix('{tenant_slug}')->group(function () {
    Route::post('/open/logout', [AuthController::class, 'logout']);
    Route::get('/open/me', [AuthController::class, 'me']);
});

Route::middleware('apikey')->post('/invoice/generate',[InvoiceController::class,'generate']);
Route::middleware('apikey')->get('/invoice/view/{uuid}',[InvoiceController::class,'generatedView']);

Route::middleware('apikey')->get('/tenant/info',[InfoController::class,'index']);
Route::get('/tenant/list',[InfoController::class,'list']);


Route::get('/invoice/{uuid}/pdf',[InvoiceController::class,'downloadPdf'])->name('invoice.pdf');
Route::get('/invoice/{uuid}',[InvoiceController::class,'view']);
Route::get('/token/{uuid}',[InvoiceController::class,'viewToken']);


Route::post('/phonepe/callback', [PhonePeController::class, 'callback'])
    ->name('phonepe.callback');


Route::post('/reviews', [
    ReviewController::class,
    'submit'
]);

Route::post('/feedback', [
    ReviewController::class,
    'submit'
]);

Route::get('/reviews/{slug}', [
    ReviewController::class,
    'tenantReviews'
]);
