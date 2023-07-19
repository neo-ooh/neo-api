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
use Neo\Http\Controllers\CensusDivisionsController;
use Neo\Http\Controllers\CensusFederalElectoralDistrictsController;
use Neo\Http\Controllers\CensusForwardSortationAreaController;
use Neo\Http\Controllers\CensusSubdivisionsController;
use Neo\Http\Controllers\FoursquareController;
use Neo\Http\Controllers\GoogleMapsController;
use Neo\Http\Controllers\ModulesController;
use Neo\Http\Controllers\StatsController;
use Neo\Http\Controllers\TimezonesController;
use Neo\Models\CensusDivision;
use Neo\Models\CensusFederalElectoralDistrict;
use Neo\Models\CensusForwardSortationArea;
use Neo\Models\CensusSubdivision;

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
    |----------------------------------------------------------------------
    | Timezones
    |----------------------------------------------------------------------
    */

    Route::get("timezones", [TimezonesController::class, "index"]);

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
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    Route::model("censusSubdivision", CensusSubdivision::class);
    Route::get("census-subdivisions", [CensusSubdivisionsController::class, "index"]);
    Route::get("census-subdivisions/{censusSubdivision}", [CensusSubdivisionsController::class, "show"]);

    Route::model("censusDivision", CensusDivision::class);
    Route::get("census-divisions", [CensusDivisionsController::class, "index"]);
    Route::get("census-divisions/{censusDivision}", [CensusDivisionsController::class, "show"]);

    Route::model("censusForwardSortationArea", CensusForwardSortationArea::class);
    Route::get("census-fsas", [CensusForwardSortationAreaController::class, "index"]);
    Route::get("census-fsas/{censusForwardSortationArea}", [CensusForwardSortationAreaController::class, "show"]);

    Route::model("censusFederalElectoralDistrict", CensusFederalElectoralDistrict::class);
    Route::get("census-federal-electoral-districts", [CensusFederalElectoralDistrictsController::class, "index"]);
    Route::get("census-federal-electoral-districts/{censusFederalElectoralDistrict}", [CensusFederalElectoralDistrictsController::class, "show"]);

    /*
    |----------------------------------------------------------------------
    | Batch
    |----------------------------------------------------------------------
    */

    Route::post("batch", BatchController::class . "@handle");
});
