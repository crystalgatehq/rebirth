<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        parent::boot();

        // Horizon::routeSmsNotificationsTo('15556667777');
        // Horizon::routeMailNotificationsTo('example@example.com');
        // Horizon::routeSlackNotificationsTo('slack-webhook-url', '#channel');
    }

    /**
     * Register the Horizon gate.
     *
     * This gate determines who can access Horizon in non-local environments.
     */
    protected function gate(): void
    {
        // Gate::define('viewHorizon', function ($user = null) {
        //     return in_array(optional($user)->email, [
        //         'superadmin@psilocybin.org' => 'super_administrator',
        //         'gm@psilocybin.org' => 'general_manager',
        //         'finance@psilocybin.org' => 'finance_manager',
        //         'operations@psilocybin.org' => 'operations_manager',
        //         'restaurant@psilocybin.org' => 'restaurant_manager',
        //         'bar@psilocybin.org' => 'bar_manager',
        //         'chef@psilocybin.org' => 'executive_chef',
        //         'accommodation@psilocybin.org' => 'accommodation_manager',
        //         'hr@psilocybin.org' => 'hr_manager',
        //         'security@psilocybin.org' => 'security_manager',
        //         'guard@psilocybin.org' => 'security_guard',
        //         'frontdesk@psilocybin.org' => 'front_desk_agent',
        //         'server@psilocybin.org' => 'server',
        //         'bartender@psilocybin.org' => 'bartender',
        //         'housekeeping@psilocybin.org' => 'housekeeping',
        //     ]);
        // });

        Gate::define('viewHorizon', function ($user = null) {
            return true;
        });
    }
}
