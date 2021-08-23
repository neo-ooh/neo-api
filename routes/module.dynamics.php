<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.dynamics.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\NewsBackgroundsController;
use Neo\Http\Controllers\NewsController;
use Neo\Http\Controllers\WeatherBackgroundsController;
use Neo\Http\Controllers\WeatherController;
use Neo\Http\Controllers\WeatherLocationsController;
use Neo\Models\NewsBackground;
use Neo\Models\WeatherBackground;
use Neo\Models\WeatherLocation;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1/dynamics"
], function () {
    // News -------------------------
    Route::model("newsBackground", NewsBackground::class);

    Route::get("news/backgrounds", NewsBackgroundsController::class . "@index");
    Route::post("news/backgrounds", NewsBackgroundsController::class . "@store");
    Route::delete("news/backgrounds/{newsBackground}", NewsBackgroundsController::class . "@destroy");

    // Weather ----------------------
    Route::model("weatherBackground", WeatherBackground::class);

    Route::get("weather/backgrounds", WeatherBackgroundsController::class . "@index");
    Route::post("weather/backgrounds", WeatherBackgroundsController::class . "@store");
    Route::delete("weather/backgrounds/{weatherBackground}", WeatherBackgroundsController::class . "@destroy");

    Route::model("weatherLocation", WeatherLocation::class);

    Route::get("weather/locations", WeatherLocationsController::class . "@index");
    Route::get("weather/locations/{country}/{province}/{city}", WeatherLocationsController::class . "@show");
    Route::put("weather/locations/{weatherLocation}", WeatherLocationsController::class . "@update");
});

Route::group([
    "middleware" => ['default', 'dynamics'],
    "prefix"     => "v1/dynamics"
], function () {
    Route::prefix("_news")->group(function () {
        Route::get("records", NewsController::class . "@index");
        Route::get("backgrounds", NewsBackgroundsController::class . "@index");
    });

    Route::prefix("_weather")->group(function () {
        Route::get("locations/{country}/{province}/{city}", WeatherLocationsController::class . "@show");
        Route::get("backgrounds", WeatherBackgroundsController::class . "@index");
        Route::get("national", WeatherController::class . "@national");
        Route::get("current", WeatherController::class . "@current");
        Route::get("next-day", WeatherController::class . "@nextDay");
        Route::get("forecast", WeatherController::class . "@forecast");
        Route::get("hourly", WeatherController::class . "@hourly");
    });
});
