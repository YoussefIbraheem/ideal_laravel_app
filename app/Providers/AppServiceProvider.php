<?php

namespace App\Providers;

use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        UserResource::withoutWrapping();

      RateLimiter::for('api', function (Request $request) {
        return $request->user() ?
        Limit::perMinute(60) :
        Limit::perMinute(10)->by($request->ip());
    });
    }
}
