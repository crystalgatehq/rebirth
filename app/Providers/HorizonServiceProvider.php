<?php

namespace App\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Gate;
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
        //         'superadmin@rebirth.org' => 'super_administrator',
        //         'gm@rebirth.org' => 'general_manager',
        //         'finance@rebirth.org' => 'finance_manager',
        //         'operations@rebirth.org' => 'operations_manager',
        //         'restaurant@rebirth.org' => 'restaurant_manager',
        //         'bar@rebirth.org' => 'bar_manager',
        //         'chef@rebirth.org' => 'executive_chef',
        //         'accommodation@rebirth.org' => 'accommodation_manager',
        //         'hr@rebirth.org' => 'hr_manager',
        //         'security@rebirth.org' => 'security_manager',
        //         'guard@rebirth.org' => 'security_guard',
        //         'frontdesk@rebirth.org' => 'front_desk_agent',
        //         'server@rebirth.org' => 'server',
        //         'bartender@rebirth.org' => 'bartender',
        //         'housekeeping@rebirth.org' => 'housekeeping',
        //     ]);
        // });

        Gate::define('viewHorizon', function ($user = null) {
            return true;
        });
    }
}
