<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ThirdPartiesServicesProvider.php
 */

namespace Neo\Providers;

use Illuminate\Support\ServiceProvider;
use Neo\Services\News\CanadianPressInterface;
use Neo\Services\News\NewsService;
use Neo\Services\Weather\MeteoMediaInterface;
use Neo\Services\Weather\WeatherService;

class ThirdPartiesServicesProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Bind the MeteoMedia interface to the Weather service
        $this->app->bind(WeatherService::class, MeteoMediaInterface::class);

        // Bind the Canadian Press interface to the News service
        $this->app->bind(NewsService::class, CanadianPressInterface::class);
    }
}
