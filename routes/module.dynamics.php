<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.dynamics.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Models\NewsBackground;
use Neo\Models\WeatherBackground;
use Neo\Models\WeatherLocation;

Route::group([
	             "middleware" => "default",
	             "prefix"     => "v1/dynamics",
             ], function () {
	// News -------------------------
	Route::model("newsBackground", NewsBackground::class);

	// Weather ----------------------
	Route::model("weatherBackground", WeatherBackground::class);

	Route::model("weatherLocation", WeatherLocation::class);

});
