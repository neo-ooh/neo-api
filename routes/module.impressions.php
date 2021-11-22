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
use Neo\Http\Controllers\ImpressionsModelsController;
use Neo\Http\Controllers\TrafficController;
use Neo\Models\DisplayTypePrintsFactors;
use Neo\Models\ImpressionsModel;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1"
], function () {

    /*
    |----------------------------------------------------------------------
    | Calculations
    |----------------------------------------------------------------------
    */

    Route::model("factors", DisplayTypePrintsFactors::class);

    Route:: get("display-types-factors", DisplayTypesPrintsFactorsController::class . "@index");
    Route::post("display-types-factors", DisplayTypesPrintsFactorsController::class . "@store");
    Route:: put("display-types-factors/{factors}", DisplayTypesPrintsFactorsController::class . "@update");

    /*
    |----------------------------------------------------------------------
    | Impressions Models
    |----------------------------------------------------------------------
    */

    Route::model("impressionsModel", ImpressionsModel::class);

    Route::   get("product-categories/{modelsHolder}/impressions-models", ImpressionsModelsController::class . "@show");
    Route::  post("product-categories/{modelsHolder}/impressions-models", ImpressionsModelsController::class . "@store");
    Route::   put("product-categories/{modelsHolder}/impressions-models/{impressionsModel}", ImpressionsModelsController::class . "@update");
    Route::delete("product-categories/{modelsHolder}/impressions-models/{impressionsModel}", ImpressionsModelsController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Traffic export
    |----------------------------------------------------------------------
    */

    Route::post("traffic/_export", TrafficController::class . "@export");
});
