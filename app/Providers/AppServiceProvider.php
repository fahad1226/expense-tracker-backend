<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
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
        Authenticate::redirectUsing(function (\Illuminate\Http\Request $request): ?string {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            return null;
        });

        RedirectIfAuthenticated::redirectUsing(fn () => route('admin.dashboard'));
    }
}
