<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ThirdPartyServicesServiceProvider.php
 */

namespace Neo\Modules\Dynamics\Providers;

use Illuminate\Support\ServiceProvider;
use Neo\Modules\Dynamics\Services\News\CanadianPressClient;
use Neo\Modules\Dynamics\Services\News\NewsAdapter;
use Neo\Modules\Dynamics\Services\Weather\WeatherAdapter;
use Neo\Modules\Dynamics\Services\Weather\WeatherSourceClient;

class ThirdPartyServicesServiceProvider extends ServiceProvider {
	public function register(): void {
		// Weather
		$this->app->bind(WeatherAdapter::class, WeatherSourceClient::class);

		// News
		$this->app->bind(NewsAdapter::class, CanadianPressClient::class);
	}

	public function boot(): void {
	}
}
