<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RouteServiceProvider.php
 */

namespace Neo\Modules\Properties\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider {
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected string $moduleNamespace = 'Neo\Modules\Properties\Http\Controllers';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot(): void {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map(): void {
        $this->mapApiRoutes();
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes(): void {
        Route::group([], module_path('Properties', '/Routes/properties.php'));
        Route::group([], module_path('Properties', '/Routes/products.php'));
        Route::group([], module_path('Properties', '/Routes/fields.php'));
        Route::group([], module_path('Properties', '/Routes/demographics.php'));
        Route::group([], module_path('Properties', '/Routes/impressions.php'));
        Route::group([], module_path('Properties', '/Routes/unavailabilities.php'));
    }
}
