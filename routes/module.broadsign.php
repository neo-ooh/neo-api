<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.broadsign.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\BroadSignCriteriaController;
use Neo\Http\Controllers\BroadSignSeparationsController;
use Neo\Http\Controllers\BroadSignTriggersController;
use Neo\Http\Controllers\ContractBurstsController;
use Neo\Models\BroadSignCriteria;
use Neo\Models\BroadSignSeparation;
use Neo\Models\BroadSignTrigger;
use Neo\Models\ContractBurst;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1/broadsign"
], function () {
    /*
    |--------------------------------------------------------------------------
    | Criteria
    |--------------------------------------------------------------------------
    */

    Route::model("criteria", BroadSignCriteria::class);

    Route::   get("criteria", BroadSignCriteriaController::class . "@index");
    Route::  post("criteria", BroadSignCriteriaController::class . "@store");
    Route::   get("criteria/{criteria}", BroadSignCriteriaController::class . "@show");
    Route::   put("criteria/{criteria}", BroadSignCriteriaController::class . "@update");
    Route::delete("criteria/{criteria}", BroadSignCriteriaController::class . "@destroy");

    /*
    |--------------------------------------------------------------------------
    | Triggers
    |--------------------------------------------------------------------------
    */

    Route::model("trigger", BroadSignTrigger::class);

    Route::   get("triggers", BroadSignTriggersController::class . "@index");
    Route::  post("triggers", BroadSignTriggersController::class . "@store");
    Route::   get("triggers/{trigger}", BroadSignTriggersController::class . "@show");
    Route::   put("triggers/{trigger}", BroadSignTriggersController::class . "@update");
    Route::delete("triggers/{trigger}", BroadSignTriggersController::class . "@destroy");

    /*
    |--------------------------------------------------------------------------
    | Separations
    |--------------------------------------------------------------------------
    */

    Route::model("separation", BroadSignSeparation::class);

    Route::   get("separations", BroadSignSeparationsController::class . "@index");
    Route::  post("separations", BroadSignSeparationsController::class . "@store");
    Route::   get("separations/{separation}", BroadSignSeparationsController::class . "@show");
    Route::   put("separations/{separation}", BroadSignSeparationsController::class . "@update");
    Route::delete("separations/{separation}", BroadSignSeparationsController::class . "@destroy");
});


Route::group([
    "middleware" => "broadsign",
    "prefix"     => "v1/broadsign"
], function () {
    /*
    |--------------------------------------------------------------------------
    | Bursts
    |--------------------------------------------------------------------------
    */

    Route::model("burst", ContractBurst::class);

    Route::post("burst_callback/{burst}", ContractBurstsController::class . "@receive");
});
