<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.misc.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\BatchController;
use Neo\Http\Controllers\FoursquareController;
use Neo\Http\Controllers\GoogleMapsController;
use Neo\Http\Controllers\ModulesController;
use Neo\Http\Controllers\StatsController;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ], function () {
    /*
    |----------------------------------------------------------------------
    | Modules
    |----------------------------------------------------------------------
    */

    Route::get("modules/_status", ModulesController::class . "@status");


    /*
    |----------------------------------------------------------------------
    | Stats
    |----------------------------------------------------------------------
    */

    Route::get("stats", StatsController::class . "@index");

    /*
    |--------------------------------------------------------------------------
    | Google Maps
    |--------------------------------------------------------------------------
    */

    Route::get("google/places", GoogleMapsController::class . "@_searchPlaces");

    /*
    |--------------------------------------------------------------------------
    | Foursquare
    |--------------------------------------------------------------------------
    */

    Route::get("_third-party/foursquare/places", FoursquareController::class . "@_searchPlaces");

    /*
    |----------------------------------------------------------------------
    | Batch
    |----------------------------------------------------------------------
    */

    Route::post("batch", BatchController::class . "@handle");
});
