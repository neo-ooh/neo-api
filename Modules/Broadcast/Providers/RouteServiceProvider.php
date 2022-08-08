<?php

namespace Neo\Modules\Broadcast\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider {
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected string $moduleNamespace = 'Neo\Modules\Broadcast\Http\Controllers';

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
        Route::group([], module_path('Broadcast', '/Routes/broadcast-tags.php'));
        Route::group([], module_path('Broadcast', '/Routes/campaigns.php'));
        Route::group([], module_path('Broadcast', '/Routes/formats.php'));
        Route::group([], module_path('Broadcast', '/Routes/libraries.php'));
        Route::group([], module_path('Broadcast', '/Routes/networks.php'));
    }
}
