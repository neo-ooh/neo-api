<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - weather.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Dynamics\Http\Controllers\WeatherBundleBackgroundsController;
use Neo\Modules\Dynamics\Http\Controllers\WeatherBundlesController;
use Neo\Modules\Dynamics\Http\Controllers\WeatherDataController;
use Neo\Modules\Dynamics\Models\WeatherBundle;
use Neo\Modules\Dynamics\Models\WeatherBundleBackground;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group([
	             "middleware" => "default",
	             "prefix"     => "v1",
             ],
	static function () {
		/*
		|----------------------------------------------------------------------
		| Weather Bundles
		|----------------------------------------------------------------------
		*/

		Route::model("weatherBundle", WeatherBundle::class);

		Route::   get("dynamics/weather/bundles/", [WeatherBundlesController::class, "index"]);
		Route::  post("dynamics/weather/bundles/", [WeatherBundlesController::class, "store"]);
		Route::   get("dynamics/weather/bundles/match", [WeatherBundlesController::class, "match"]);
		Route::   get("dynamics/weather/bundles/{weatherBundle}", [WeatherBundlesController::class, "show"]);
		Route::   put("dynamics/weather/bundles/{weatherBundle}", [WeatherBundlesController::class, "update"]);
		Route::delete("dynamics/weather/bundles/{weatherBundle}", [WeatherBundlesController::class, "destroy"]);


		/*
		|----------------------------------------------------------------------
		| Weather Backgrounds
		|----------------------------------------------------------------------
		*/

		Route::model("weatherBackground", WeatherBundleBackground::class);

		Route::   get("dynamics/weather/bundles/{weatherBundle}/backgrounds", [WeatherBundleBackgroundsController::class, "index"]);
		Route::  post("dynamics/weather/bundles/{weatherBundle}/backgrounds", [WeatherBundleBackgroundsController::class, "store"]);
		Route::   get("dynamics/weather/bundles/{weatherBundle}/backgrounds/{weatherBackground}", [WeatherBundleBackgroundsController::class, "show"]);
		Route::delete("dynamics/weather/bundles/{weatherBundle}/backgrounds/{weatherBackground}", [WeatherBundleBackgroundsController::class, "destroy"]);

		/*
		|----------------------------------------------------------------------
		| Weather Data
		|----------------------------------------------------------------------
		*/

		Route:: get("dynamics/weather/data/{city}", [WeatherDataController::class, "show"]);
	});
