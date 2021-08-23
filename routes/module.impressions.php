<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.impressions.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\DisplayTypesPrintsFactorsController;
use Neo\Http\Controllers\TrafficController;
use Neo\Models\DisplayTypePrintsFactors;

Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {

    /*
    |----------------------------------------------------------------------
    | Calculations
    |----------------------------------------------------------------------
    */

    Route::model("factors", DisplayTypePrintsFactors::class);

    Route:: get("display-types-factors"          , DisplayTypesPrintsFactorsController::class . "@index");
    Route::post("display-types-factors"          , DisplayTypesPrintsFactorsController::class . "@store");
    Route:: put("display-types-factors/{factors}", DisplayTypesPrintsFactorsController::class . "@update");

    /*
    |----------------------------------------------------------------------
    | Traffic export
    |----------------------------------------------------------------------
    */

    Route::post("traffic/_export", TrafficController::class . "@export");
});
