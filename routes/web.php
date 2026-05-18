<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tenant\InfoController;
use App\Http\Controllers\PublicBillingController;

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
