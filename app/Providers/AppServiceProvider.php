<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        Gate::define('permission', function (User $user, string $slug): bool {
            return $user->canPermission($slug);
        });

        View::composer('layouts.admin', function (): void {
            auth()->user()?->loadMissing('role.permissions');
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(3000)->by($request->ip() ?: 'api');
        });
    }
}
