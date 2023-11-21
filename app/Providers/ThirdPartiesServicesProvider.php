<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
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

class ThirdPartiesServicesProvider extends ServiceProvider {
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
		// Bind the Canadian Press interface to the News service
		$this->app->bind(NewsService::class, CanadianPressInterface::class);
	}
}
