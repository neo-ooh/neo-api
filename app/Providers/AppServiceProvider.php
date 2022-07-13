<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AppServiceProvider.php
 */

namespace Neo\Providers;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Support\ServiceProvider;
use Neo\Http\Controllers\CreativesController;
use Neo\Modules\Broadcast\Models\Creative;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void {
        // Register convenient FFMpeg initializer
        $this->app->when([Creative::class, CreativesController::class])
                  ->needs(FFMpeg::class)
                  ->give(fn() => FFMpeg::create(config('ffmpeg')));

        $this->app->when([Creative::class, CreativesController::class])
                  ->needs(FFProbe::class)
                  ->give(fn() => FFProbe::create(config('ffmpeg')));
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void {
        //
    }
}
