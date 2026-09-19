<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production') || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        Blade::component('layouts.guest', 'guest-layout');
        Blade::component('layouts.app', 'app-layout');

        // Dynamic Platform Control Plane Permissions Interceptor
        Gate::before(function (User $user, string $ability) {
            if (str_starts_with($ability, 'platform.')) {
                return $user->hasPlatformPermission($ability) ?: null;
            }
        });
    }
}
