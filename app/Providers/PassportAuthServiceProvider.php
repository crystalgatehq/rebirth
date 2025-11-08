<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class PassportAuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Ignore the default Passport routes
        Passport::ignoreRoutes();
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Register Passport routes
        if (!$this->app->routesAreCached()) {
            // Register the default routes
            Route::prefix('oauth')->group(function () {
                Route::post('/token', [
                    'uses' => '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken',
                    'as' => 'passport.token',
                    'middleware' => 'throttle',
                ]);

                Route::get('/authorize', [
                    'uses' => '\Laravel\Passport\Http\Controllers\AuthorizationController@authorize',
                    'as' => 'passport.authorizations.authorize',
                    'middleware' => 'web',
                ]);

                // Add other routes as needed
            });
        }

        // Token Lifetimes
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}
