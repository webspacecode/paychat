<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\Tenant\ProductController;
use App\Http\Controllers\Api\Tenant\ProductImageSuggestionController;
use App\Http\Controllers\Api\Tenant\PosFavoriteProductController;
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
use App\Http\Controllers\Api\Tenant\KitchenSettingsController;
use App\Http\Controllers\Api\Tenant\TableController;
use App\Http\Controllers\Api\Tenant\TableSessionController;
use App\Http\Controllers\Api\Tenant\PhonePeController;
use App\Http\Controllers\Api\Tenant\SelfPosOrderController;
use App\Http\Controllers\Api\Tenant\SelfPosQrController;
use App\Http\Controllers\Api\Tenant\TokenController;
use App\Http\Controllers\Api\Tenant\UpiProfileController;
use App\Http\Controllers\Api\DemoLeadController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\Tenant\ReportController;
use App\Http\Controllers\Api\Tenant\OfflineOrderSyncController;
use App\Http\Controllers\Api\Tenant\BootstrapController;
use App\Http\Controllers\Api\Tenant\LoyaltySettingsController;
use App\Http\Controllers\Api\Tenant\BakeryOrderController;
use App\Http\Controllers\Api\Tenant\BakeryOrderPaymentController;
use App\Http\Controllers\Api\Tenant\ModuleSettingsController;
use App\Http\Controllers\Api\Tenant\Registration\ProgramController;
use App\Http\Controllers\Api\Tenant\Registration\ProgramBatchController;
use App\Http\Controllers\Api\Tenant\Registration\ParticipantController;
use App\Http\Controllers\Api\Tenant\Registration\ParticipantPhotoController;
use App\Http\Controllers\Api\Tenant\Registration\ProgramRegistrationController;



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
    Route::get('/tables', [TableController::class, 'index']);
    Route::get('/categories/search', [CategoryController::class, 'search']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'create']);
    Route::put('/orders/{order}/items', [OrderController::class, 'updateItems'])->whereNumber('order');
    Route::post('/orders/{order}/pending-payment', [OrderController::class, 'moveToPayment']);
    Route::post('/orders/{order}/payments', [PaymentController::class, 'createPayment']);
    Route::post('/orders/{order}/self-pos-submit', [SelfPosOrderController::class, 'submit'])->whereNumber('order');
    Route::patch('/orders/{order}/customer', [OrderController::class, 'attachCustomer'])->whereNumber('order');
    Route::post('/payments/{payment}/success', [PaymentController::class, 'markSuccess']);
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');
});

Route::post('/register-tenant', [TenantController::class, 'register']);
Route::get('/onboarding/status/{tenant_slug}', [TenantController::class, 'onboardingStatus']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['api-protected'])->prefix('{tenant_slug}')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/bootstrap', [BootstrapController::class, 'show']);
    Route::put('/settings/modules/{module}', [ModuleSettingsController::class, 'updateEnabled'])
        ->middleware('permission:tenant.modules.manage');
    Route::match(['get', 'post'], '/self-pos/qr', [SelfPosQrController::class, 'tenant'])
        ->middleware('permission:settings.manage');
    Route::get('/registration/access', [ModuleSettingsController::class, 'access'])
        ->middleware('module.access:registration_management,registration.access');
    Route::prefix('registration')->group(function () {
        Route::get('/overview', [ProgramRegistrationController::class, 'overview'])->middleware('module.access:registration_management,registration.access');
        Route::get('/registrations', [ProgramRegistrationController::class, 'index'])->middleware('module.access:registration_management,registration.registrations.view');
        Route::post('/registrations', [ProgramRegistrationController::class, 'store'])->middleware('module.access:registration_management,registration.registrations.create');
        Route::get('/registrations/{registration}', [ProgramRegistrationController::class, 'show'])->middleware('module.access:registration_management,registration.registrations.view');
        Route::put('/registrations/{registration}', [ProgramRegistrationController::class, 'update'])->middleware('module.access:registration_management,registration.registrations.update');
        Route::post('/registrations/{registration}/cancel', [ProgramRegistrationController::class, 'cancel'])->middleware('module.access:registration_management,registration.registrations.cancel');
        Route::post('/registrations/{registration}/order', [ProgramRegistrationController::class, 'generateOrder'])->middleware('module.access:registration_management,registration.registrations.update');
        Route::get('/program-products', [ProgramController::class, 'compatibleProducts'])->middleware('module.access:registration_management,registration.programs.view');
        Route::get('/programs', [ProgramController::class, 'index'])->middleware('module.access:registration_management,registration.programs.view');
        Route::post('/programs', [ProgramController::class, 'store'])->middleware('module.access:registration_management,registration.programs.create');
        Route::get('/programs/{program}', [ProgramController::class, 'show'])->middleware('module.access:registration_management,registration.programs.view');
        Route::put('/programs/{program}', [ProgramController::class, 'update'])->middleware('module.access:registration_management,registration.programs.update');
        Route::post('/programs/{program}/archive', [ProgramController::class, 'archive'])->middleware('module.access:registration_management,registration.programs.archive');
        Route::get('/programs/{program}/batches', [ProgramBatchController::class, 'index'])->middleware('module.access:registration_management,registration.programs.view');
        Route::post('/programs/{program}/batches', [ProgramBatchController::class, 'store'])->middleware('module.access:registration_management,registration.programs.create');
        Route::get('/batches/{batch}', [ProgramBatchController::class, 'show'])->middleware('module.access:registration_management,registration.programs.view');
        Route::put('/batches/{batch}', [ProgramBatchController::class, 'update'])->middleware('module.access:registration_management,registration.programs.update');
        Route::post('/batches/{batch}/archive', [ProgramBatchController::class, 'archive'])->middleware('module.access:registration_management,registration.programs.archive');
        Route::get('/instructors', [ProgramBatchController::class, 'instructors'])->middleware('module.access:registration_management,registration.programs.view');
        Route::get('/participants/customer-matches', [ParticipantController::class, 'matches'])->middleware('module.access:registration_management,registration.participants.view');
        Route::get('/participants', [ParticipantController::class, 'index'])->middleware('module.access:registration_management,registration.participants.view');
        Route::post('/participants', [ParticipantController::class, 'store'])->middleware('module.access:registration_management,registration.participants.create');
        Route::get('/participants/{participant}', [ParticipantController::class, 'show'])->middleware('module.access:registration_management,registration.participants.view');
        Route::put('/participants/{participant}', [ParticipantController::class, 'update'])->middleware('module.access:registration_management,registration.participants.update');
        Route::post('/participants/{participant}/archive', [ParticipantController::class, 'archive'])->middleware('module.access:registration_management,registration.participants.update');
        Route::get('/participants/{participant}/photo', [ParticipantPhotoController::class, 'show'])->middleware('module.access:registration_management,registration.participants.view');
        Route::post('/participants/{participant}/photo', [ParticipantPhotoController::class, 'store'])->middleware('module.access:registration_management,registration.participants.update');
        Route::delete('/participants/{participant}/photo', [ParticipantPhotoController::class, 'destroy'])->middleware('module.access:registration_management,registration.participants.update');
    });
    Route::get('/settings/registration', [ModuleSettingsController::class, 'registration'])
        ->middleware('module.access:registration_management,registration.settings.manage');
    Route::put('/settings/registration', [ModuleSettingsController::class, 'updateRegistration'])
        ->middleware('module.access:registration_management,registration.settings.manage');

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->whereNumber('customer');
    Route::get('/customers/{customer}/summary', [CustomerController::class, 'summary'])->whereNumber('customer');
    Route::get('/customers/{customer}/orders', [CustomerController::class, 'orders'])->whereNumber('customer');
    Route::get('/customers/{customer}/loyalty-transactions', [CustomerController::class, 'loyaltyTransactions'])->whereNumber('customer');

    Route::prefix('bakery')->middleware(['industry:bakery', 'feature:bakery_management', 'permission:bakery.manage'])->group(function () {
        Route::get('/orders', [BakeryOrderController::class, 'index']);
        Route::post('/orders', [BakeryOrderController::class, 'store']);
        Route::get('/orders/{order}', [BakeryOrderController::class, 'show'])->whereNumber('order');
        Route::patch('/orders/{order}', [BakeryOrderController::class, 'update'])->whereNumber('order');
        Route::patch('/orders/{order}/status', [BakeryOrderController::class, 'updateStatus'])->whereNumber('order');
        Route::post('/orders/{order}/payments', [BakeryOrderPaymentController::class, 'store'])->whereNumber('order');
        Route::patch('/orders/{order}/payments/{payment}/success', [BakeryOrderPaymentController::class, 'markSuccess'])->whereNumber('order')->whereNumber('payment');
        Route::get('/production-board', [BakeryOrderController::class, 'productionBoard']);
        Route::get('/products/search', [BakeryOrderController::class, 'products']);
        Route::post('/orders/reference-image', [BakeryOrderController::class, 'uploadReferenceImage']);
    });

    Route::get('/settings/loyalty', [LoyaltySettingsController::class, 'show']);
    Route::put('/settings/loyalty', [LoyaltySettingsController::class, 'update'])->middleware('permission:settings.manage');
    Route::get('/settings/kitchen', [KitchenSettingsController::class, 'show']);
    Route::put('/settings/kitchen', [KitchenSettingsController::class, 'update'])->middleware('permission:settings.manage');

    Route::prefix('pos-favorites')->group(function () {
        Route::get('/', [PosFavoriteProductController::class, 'index']);
        Route::post('/', [PosFavoriteProductController::class, 'store'])->middleware('permission:product.manage');
        Route::delete('/{product}', [PosFavoriteProductController::class, 'destroy'])
            ->whereNumber('product')
            ->middleware('permission:product.manage');
    });

    // Category Management
    Route::prefix('categories')->group(function () {
        Route::post('/', [CategoryController::class, 'store'])->middleware('permission:product.manage');
        Route::post('/bulk', [CategoryController::class, 'bulkUpload'])->middleware('permission:product.manage');
        Route::get('/bulk/template', [CategoryController::class, 'bulkTemplate']);
        Route::put('/{category}', [CategoryController::class, 'update'])->middleware('permission:product.manage');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->middleware('permission:product.manage');
        Route::get('/search', [CategoryController::class, 'search']);
        Route::get('/{id}', [CategoryController::class, 'show']); 
    });

    // Product Management
    Route::prefix('products')->group(function () {
        // CRUD
        Route::get('/',        [ProductController::class, 'index']);   // search/list
        Route::post('/',       [ProductController::class, 'store'])->middleware('permission:product.manage');   // create
        Route::post('/bulk',       [ProductController::class, 'bulkUpload'])->middleware('permission:product.manage');   // bulk create
        Route::post('/{product}/image-suggestions', [ProductImageSuggestionController::class, 'store'])->middleware('permission:product.manage')->whereNumber('product');
        Route::post('/{product}/image-suggestions/{suggestion}/accept', [ProductImageSuggestionController::class, 'accept'])->middleware('permission:product.manage')->whereNumber('product')->whereNumber('suggestion');
        Route::post('/{product}/image-suggestions/{suggestion}/reject', [ProductImageSuggestionController::class, 'reject'])->middleware('permission:product.manage')->whereNumber('product')->whereNumber('suggestion');
        Route::get('/{product}/stock-movements', [ProductController::class, 'stockMovements'])->middleware(['feature:inventory', 'permission:product.manage']);
        Route::get('/{id}',    [ProductController::class, 'show']);    // read one
        Route::put('/{product}',   [ProductController::class, 'update'])->middleware('permission:product.manage');  // update
        Route::delete('/{product}',[ProductController::class, 'destroy'])->middleware('permission:product.manage'); // delete

        // Inventory & Movement
        Route::post('/{product}/inventory/adjust', [ProductController::class, 'adjustInventory'])->middleware(['feature:inventory', 'permission:product.manage']);
        Route::post('/{product}/inventory/move',   [ProductController::class, 'moveStock'])->middleware(['feature:inventory', 'permission:product.manage']);

        // Image Upload
        Route::post('/images/bulk',       [ProductController::class, 'bulkImageUpload'])->middleware('permission:product.manage');   // bulk image upload
    });

    // Location Management
    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index']);       // List locations
        Route::post('/', [LocationController::class, 'store']);      // Create location
        Route::get('/{id}', [LocationController::class, 'show']);    // Get location details
        Route::put('/{id}', [LocationController::class, 'update']);  // Update location
        Route::delete('/{id}', [LocationController::class, 'destroy']); // Delete location
    });

    Route::prefix('tables')->middleware(['feature:dine_in', 'permission:table.manage'])->group(function () {
        Route::get('/', [TableController::class, 'index']);
        Route::post('/', [TableController::class, 'store']);
        Route::get('/{table}/self-pos-qr', [SelfPosQrController::class, 'table'])->middleware('permission:settings.manage')->whereNumber('table');
        Route::match(['put', 'patch'], '/{table}', [TableController::class, 'update'])->whereNumber('table');
        Route::patch('/{table}/status', [TableController::class, 'updateStatus'])->whereNumber('table');
        Route::post('/{table}/release', [TableController::class, 'release'])->whereNumber('table');
    });

    Route::prefix('dining-structure')->middleware(['feature:dine_in', 'permission:table.manage'])->group(function () {
        Route::get('/', [DiningStructureController::class, 'index']);
        Route::post('/tables/bulk', [DiningStructureController::class, 'bulkUpsert']);
        Route::patch('/tables/{table}/position', [DiningStructureController::class, 'updatePosition'])->whereNumber('table');
    });

    Route::prefix('table-sessions')->middleware(['feature:dine_in', 'permission:table.manage'])->group(function () {
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
    Route::post('/orders', [OrderController::class, 'create'])->middleware(['feature:pos', 'permission:order.create']);

    // 2️⃣ Sync Items (Recommended instead of add one by one)
    Route::put('/orders/{order}/items', [OrderController::class, 'updateItems'])->middleware(['feature:pos', 'permission:order.edit'])->whereNumber('order');

    // If you still want single item endpoint
    // Route::post('/orders/{order}/items', [OrderController::class, 'addItem']);

    // Attach Customer
    Route::patch('/orders/{order}/customer', [OrderController::class, 'attachCustomer'])->middleware(['feature:pos', 'permission:order.edit']);
    Route::patch('/orders/{order}/delivery-source', [OrderController::class, 'updateDeliverySource'])->middleware(['feature:pos', 'permission:order.edit'])->whereNumber('order');

    Route::patch('/orders/{order}/table', [OrderController::class, 'assignTable'])->middleware(['feature:dine_in', 'permission:table.manage'])->whereNumber('order');
    Route::post('/orders/{order}/tables/link', [OrderController::class, 'linkTables'])->middleware(['feature:dine_in', 'permission:table.manage'])->whereNumber('order');
    Route::post('/orders/{order}/send-to-kitchen', [OrderController::class, 'sendToKitchen'])->middleware(['feature:kds', 'permission:kds.update'])->whereNumber('order');
    Route::post('/orders/{order}/print-kot', [OrderController::class, 'printKot'])->middleware(['feature:kds', 'permission:kds.update'])->whereNumber('order');
    Route::post('/orders/{order}/inline-token', [InlineTokenController::class, 'store'])->middleware(['feature:token_management', 'permission:order.edit'])->whereNumber('order');

    // Cancel Order
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->middleware('permission:order.cancel')->whereNumber('order');

    // 3️⃣ Move To Pending Payment
    Route::post('/orders/{order}/pending-payment', [OrderController::class, 'moveToPayment']);
    Route::post('/orders/{order}/self-pos-submit', [SelfPosOrderController::class, 'submit'])->middleware(['feature:pos', 'permission:order.create'])->whereNumber('order');
    Route::post('/orders/{order}/confirm-self-pos-payment', [SelfPosOrderController::class, 'confirmPayment'])->middleware(['feature:pos', 'permission:payment.collect'])->whereNumber('order');

    // 3️⃣ Complete Payment
    Route::post('/orders/{order}/payments', [PaymentController::class, 'createPayment'])->middleware(['feature:pos', 'permission:payment.collect']);

    Route::get('/payments/methods', [PaymentController::class, 'list']);

    Route::get('/upi-profiles', [UpiProfileController::class, 'index']);
    Route::post('/upi-profiles', [UpiProfileController::class, 'store'])->middleware('permission:settings.manage');
    Route::patch('/upi-profiles/{profile}', [UpiProfileController::class, 'update'])->middleware('permission:settings.manage')->whereNumber('profile');
    Route::delete('/upi-profiles/{profile}', [UpiProfileController::class, 'destroy'])->middleware('permission:settings.manage')->whereNumber('profile');
    Route::patch('/upi-profiles/{profile}/default', [UpiProfileController::class, 'makeDefault'])->middleware('permission:settings.manage')->whereNumber('profile');

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/inventory', [InventoryController::class, 'index'])->middleware(['feature:inventory', 'permission:product.manage']);
    Route::post('/offline-orders/sync', [OfflineOrderSyncController::class, 'sync']);

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    // 5️⃣ Payment Success Callback
    Route::post('/payments/{payment}/success', [PaymentController::class, 'markSuccess'])->middleware(['feature:pos', 'permission:payment.collect']);

    // 6️⃣ Final Complete (manual completion if needed)
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete']);

    Route::get('/orders/list', [OrderController::class, 'index']);
    Route::get('/orders/kitchen', [OrderController::class, 'kitchenIndex'])->middleware(['feature:kds', 'permission:kds.access']);

    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware(['feature:kds', 'permission:kds.update']);
    Route::get('/orders/{order}/kitchen-batches', [OrderController::class, 'kitchenBatches'])->middleware(['feature:kds', 'permission:kds.access'])->whereNumber('order');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');



    Route::prefix('tokens')->group(function () {
        Route::get('{token}', [TokenController::class, 'show'])->middleware(['feature:token_management', 'permission:kds.access']);
        Route::post('{token}/status', [TokenController::class, 'updateStatus'])->middleware(['feature:token_management', 'permission:kds.update']);
    });

    Route::get('/kitchen/orders', [KitchenController::class, 'index'])->middleware(['feature:kds', 'permission:kds.access']);
    Route::get('/kitchen/queue', [KitchenQueueController::class, 'index'])->middleware(['feature:kds', 'permission:kds.access']);
    Route::patch('/kitchen-batches/{batch}/status', [KitchenBatchController::class, 'updateStatus'])->middleware(['feature:kds', 'permission:kds.update'])->whereNumber('batch');
    Route::post('/kitchen-batches/{batch}/reprint', [KitchenBatchController::class, 'reprint'])->middleware(['feature:kds', 'permission:kds.update'])->whereNumber('batch');
    Route::post('/kitchen-batches/{batch}/cancel', [KitchenBatchController::class, 'cancel'])->middleware(['feature:kds', 'permission:kds.update'])->whereNumber('batch');


    Route::prefix('reports')->middleware(['feature:reports', 'permission:report.view'])->group(function () {
        Route::get('/summary', [ReportController::class, 'summary']);
        Route::get('/payments', [ReportController::class, 'payments']);
        Route::get('/top-products', [ReportController::class, 'topProducts']);
        Route::get('/hourly', [ReportController::class, 'hourly']);
        Route::get('/billing-by-user', [ReportController::class, 'billingByUser']);
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
