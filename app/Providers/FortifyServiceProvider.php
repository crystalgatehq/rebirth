<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\FailedTwoFactorLoginResponse;
use App\Http\Responses\TwoFactorChallengeViewResponse;
use App\Http\Responses\TwoFactorLoginResponse;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts;
use Laravel\Fortify\Fortify;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Use Fortify's built-in response classes
        $this->app->singleton(\Laravel\Fortify\Contracts\PasswordConfirmedResponse::class, 
            \Laravel\Fortify\Http\Responses\PasswordConfirmedResponse::class
        );

        $this->app->singleton(\Laravel\Fortify\Contracts\ConfirmPasswordViewResponse::class,
            \App\Http\Responses\ConfirmPasswordViewResponse::class
        );

        // Bind our custom TwoFactorChallengeViewResponse
        $this->app->singleton(
            \Laravel\Fortify\Contracts\TwoFactorChallengeViewResponse::class,
            TwoFactorChallengeViewResponse::class
        );

        // Bind our custom TwoFactorLoginResponse
        $this->app->singleton(
            \Laravel\Fortify\Contracts\TwoFactorLoginResponse::class,
            TwoFactorLoginResponse::class
        );

        // Bind our custom FailedTwoFactorLoginResponse
        $this->app->singleton(
            \Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse::class,
            FailedTwoFactorLoginResponse::class
        );
    }
    
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::confirmPasswordsUsing(function ($user) {
            if (! Auth::validate([
                'email' => $user->email,
                'password' => request()->password,
            ])) {
                throw ValidationException::withMessages([
                    'password' => __('auth.password'),
                ]);
            }

            return true;
        });
        
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
