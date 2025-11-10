<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\Communication;
use App\Observers\CommunicationObserver;
use App\Console\Commands\CheckCommunicationReceiptsStatus;
use App\Channels\SmsChannel;
use App\Services\IAN\AfricaIsTalkingServices;

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
        
        // Bind AfricaIsTalkingServices for dependency injection
        $this->app->bind(
            \App\Services\IAN\AfricaIsTalkingServices::class
        );
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

        // Register the SMS channel with Africa's Talking service
        $this->app->when(SmsChannel::class)
            ->needs(AfricaIsTalkingServices::class)
            ->give(function () {
                return new AfricaIsTalkingServices();
            });
        
        // Register the communication observer
        Communication::observe(CommunicationObserver::class);
    }
}
