<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('users.invite', function ($user, $orgId) {
            return $user->hasPermission($orgId, 'users.invite');
        });

        Gate::define('analytics.read', function ($user, $orgId) {
            return $user->hasPermission($orgId, 'analytics.read');
        });

        Gate::define('users.update', function ($user, $orgId) {
            return $user->hasPermission($orgId, 'users.update');
        });

        Gate::define('users.delete', function ($user, $orgId) {
            return $user->hasPermission($orgId, 'users.delete');
        });

        Gate::define('users.read', function ($user, $orgId) {
            return $user->hasPermission($orgId, 'users.read');
        });
    }

}
