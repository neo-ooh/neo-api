<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - documents.php
 */

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

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\NewsBackgroundsController;
use Neo\Http\Controllers\NewsController;
use Neo\Http\Controllers\WeatherController;

Route::prefix("dynamics")->group(function () {
    Route::prefix("_weather")
        /*->middleware(DynamicsMiddleware::class)*/ ->group(function () {
            Route::get("national", WeatherController::class . "@national")->name("dynamics.weather.national");
            Route::get("current", WeatherController::class . "@current")->name("dynamics.weather.current");
            Route::get("nextDay", WeatherController::class . "@nextDay")->name("dynamics.weather.next-day");
            Route::get("forecast", WeatherController::class . "@forecast")->name("dynamics.weather.forecast");
            Route::get("hourly", WeatherController::class . "@hourly")->name("dynamics.weather.hourly");
        });

    Route::prefix("_news")
        /*->middleware(DynamicsMiddleware::class)*/ ->group(function () {
            Route::get("records", NewsController::class . "@index")->name("dynamics.news.index");
            Route::get("backgrounds", NewsBackgroundsController::class . "@index")->name("dynamics.news.backgrounds");
        });
});
