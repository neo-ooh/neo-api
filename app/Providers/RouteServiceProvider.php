<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
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
use Neo\Http\Controllers\StatusController;

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

        // Register our status route
        Route::group([
                         "middleware" => "guests",
                     ], static function () {
            Route:: get('/_status', StatusController::class . '@getStatus');
        });

        $this->routes(function () {
            // Guests and not fully authenticated routes
            Route::group([], base_path('routes/core.auth.php'));

            // Core modules
            Route::group([], base_path('routes/core.actors.php'));
            Route::group([], base_path('routes/core.address.php'));
            Route::group([], base_path('routes/core.networks.php'));
            Route::group([], base_path('routes/core.parameters.php'));
            Route::group([], base_path('routes/core.roles.php'));

            Route::group([], base_path('routes/core.misc.php'));
            Route::group([], base_path('routes/module.third-parties.php'));

            // Now, we loop all modules to load their routes
            foreach (config('modules-legacy') as $module => $config) {
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
            Route::get("/_heartbeat", static fn() => new Response());
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting(): void {
        RateLimiter::for('api',
            static function (Request $request) {
                $user       = $request->user();
                $identifier = $user->id ?? $request->ip();

                return Limit::perMinute($user ? 256 : 60)->by($identifier);
            });
    }
}
