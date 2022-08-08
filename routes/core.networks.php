<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.networks.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ActorsLocationsController;
use Neo\Http\Controllers\DisplayTypesController;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1"
], static function () {

    /*
    |----------------------------------------------------------------------
    | Actors Locations
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/locations", ActorsLocationsController::class . "@index");
    Route::put("actors/{actor}/locations", ActorsLocationsController::class . "@sync");
});
