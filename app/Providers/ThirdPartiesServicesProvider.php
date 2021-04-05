<?php

namespace Neo\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
