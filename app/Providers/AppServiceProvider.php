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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (auth()->check()) {
            $tenant = auth()->user()->tenant;

            App::singleton(
                \App\Services\ProductManagement\Contracts\ProductStrategyInterface::class,
                fn () => ProductStrategyResolver::resolve($tenant->industry)
            );
        }
    }
}
