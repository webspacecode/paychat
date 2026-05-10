<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Tenant\InfoController;

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