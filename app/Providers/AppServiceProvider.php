<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
    public function boot()
    {
        Gate::define('admin-only', function (User $user) {
            return in_array($user->role, ['admin', 'super_admin']);
        });

        Gate::define('super-admin-only', function (User $user) {
            return $user->role === 'super_admin';
        });
    }
}