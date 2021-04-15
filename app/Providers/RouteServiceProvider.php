<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RouteServiceProvider.php
 */

namespace Neo\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * Class RouteServiceProvider
 *
 * @package Neo\Providers
 */
class RouteServiceProvider extends ServiceProvider {
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot (): void {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Heartbeat route for up-time monitoring
            Route::get("/_heartbeat", fn() => new Response())->name("heartbeat");

            Route::middleware('default')->group(base_path('routes/api.php'));

            Route::middleware('guests')->group(base_path('routes/auth.php'));

            Route::middleware('broadsign')->group(base_path('routes/broadsign.php'));

            Route::middleware('access-tokens')->group(function () {
                Route::group([], base_path('routes/documents.php'));
                Route::group([], base_path('routes/dynamics.php'));
            });
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting (): void {
        RateLimiter::for('api',
            function (Request $request) {
                return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
            });
    }
}
