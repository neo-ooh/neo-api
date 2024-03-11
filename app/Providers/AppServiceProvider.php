<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AppServiceProvider.php
 */

namespace Neo\Providers;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\ServiceProvider;
use Neo\Helpers\PublicRelations;


class AppServiceProvider extends ServiceProvider {
	public array $helpers = [
		"Helpers/models.php",
		"Helpers/array.php",
		"Helpers/aspectRatio.php",
		"Helpers/param.php",
		"Helpers/numbers.php",
		"Helpers/debug.php",
	];

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register(): void {
		// Register convenient FFMpeg/FFProbe initializer
		$this->app->bind(FFMpeg::class, fn() => FFMpeg::create(config('ffmpeg')));
		$this->app->bind(FFProbe::class, fn() => FFProbe::create(config('ffmpeg')));

		$this->registerHelpers();
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot(): void {
		/**
		 * Load the public relations of all the models in the Collection
		 */
		Collection::macro("loadPublicRelations", function (string|array|null $relations = null) {
			PublicRelations::loadPublicRelations($this, $relations);
			return $this;
		});
	}

	protected function registerHelpers(): void {
		foreach ($this->helpers as $helperFile) {
			require_once app_path($helperFile);
		}
	}
}
