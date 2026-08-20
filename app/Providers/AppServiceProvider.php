<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ProductManagement\Strategies\ProductStrategyResolver;
use Illuminate\Support\Facades\App;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\ProductImages\Contracts\ProductImageProviderInterface::class,
            \App\Services\ProductImages\PexelsProductImageProvider::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (auth()->check() && auth()->user()->tenant) {
            $tenant = auth()->user()->tenant;

            App::singleton(
                \App\Services\ProductManagement\Contracts\ProductStrategyInterface::class,
                fn () => ProductStrategyResolver::resolve($tenant->industry)
            );
        }
    }
}
