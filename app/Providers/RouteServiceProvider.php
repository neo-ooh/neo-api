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
    public function boot(): void {
        $this->configureRateLimiting();

        $this->routes(function () {
            // Guests and not fully authenticated routes
            Route::group([], base_path('routes/core.auth.php'));

            // Core modules
            Route::group([], base_path('routes/core.actors.php'));
            Route::group([], base_path('routes/core.campaigns.php'));
            Route::group([], base_path('routes/core.formats.php'));
            Route::group([], base_path('routes/core.libraries.php'));
            Route::group([], base_path('routes/core.networks.php'));
            Route::group([], base_path('routes/core.parameters.php'));
            Route::group([], base_path('routes/core.roles.php'));

            Route::group([], base_path('routes/core.misc.php'));

            // Now, we loop all modules to load their routes
            foreach (config('modules') as $module => $config) {
                if ($module === 'core') {
                    continue;
                }

                if (!$config["enabled"]) {
                    continue;
                }

                $routesFile = base_path("routes/module.$module.php");

                if (!file_exists($routesFile)) {
                    continue;
                }

                Route::group([], $routesFile);
            }


            // Heartbeat route for up-time monitoring
            Route::get("/_heartbeat", fn() => new Response());
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void {
        RateLimiter::for('api',
            function (Request $request) {
                $user       = $request->user();
                $identifier = $user?->id ?? $request->ip();

                return Limit::perMinute($user ? 256 : 10)->by($identifier);
            });
    }
}
