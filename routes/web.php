<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tenant\InfoController;
use App\Http\Controllers\PublicBillingController;
use App\Http\Controllers\Web\AuthenticatedSessionController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\RegisteredTenantController;

Route::get('/', [InfoController::class,'welcome']);


Route::get('/features', function () {
    return view('features');
});

Route::get('/pricing', function () {
    return view('pricing');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/contact', function () {
    return view('contact');
});

Route::view('/guide', 'guide')->name('guide');

Route::get('/billing/tokens/{uuid}', [PublicBillingController::class, 'token']);
Route::get('/billing/invoices/{uuid}', [PublicBillingController::class, 'invoice']);

Route::get('/pos/{any?}', function () {
   return response()->file(public_path('pos/index.html'));
})->where('any', '.*');

use Spatie\Sitemap\SitemapGenerator;

Route::get('/generate-sitemap', function () {

    SitemapGenerator::create(config('app.url'))
        ->writeToFile(public_path('sitemap.xml'));

    return 'Sitemap generated';

});

Route::get('/store/{slug}', [InfoController::class, 'storePage']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredTenantController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredTenantController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'master'])->prefix('master')->name('master.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'master'])->name('dashboard');
    Route::patch('/tenants/{tenant}/password', [DashboardController::class, 'resetTenantPassword'])
        ->name('tenants.password');
});

Route::get('/dashboard', [DashboardController::class, 'tenant'])
    ->middleware('auth')
    ->name('tenant.dashboard');
