<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // App Environment.
        if (env('APP_ENV') === 'production') {
            // primary requirement for digital ocean MySQL network
            DB::statement('SET SESSION sql_require_primary_key=0');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // App Environment.
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        // Define a global timestamp for the request cycle
        if (!defined('REQUEST_TIMESTAMP')) {
            define('REQUEST_TIMESTAMP', now());
        }
        
        // App Db Schema.
        Schema::defaultStringLength(191);

        // Register the Product observer
        // Product::observe(ProductObserver::class);
    }
}
