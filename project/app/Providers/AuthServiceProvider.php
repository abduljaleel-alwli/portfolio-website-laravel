<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Super Admin bypasses all authorization checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        Auth::viaRequest('web', function ($request) {
            $user = Auth::user();

            if ($user && !$user->isActive()) {
                Auth::logout();
                return null;
            }

            return $user;
        });
    }
}
