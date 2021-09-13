<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.planning.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\CampaignPlannerSavesController;
use Neo\Http\Controllers\Odoo\PropertiesController;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1"
], function () {
    /*
    |----------------------------------------------------------------------
    | Actors saved planning runs
    |----------------------------------------------------------------------
    */

    Route::   get("actors/{actor}/campaign-planner-saves", CampaignPlannerSavesController::class . "@index");
    Route::  post("actors/{actor}/campaign-planner-saves", CampaignPlannerSavesController::class . "@store");
    Route::   get("actors/{actor}/campaign-planner-saves/{campaignPlannerSave}", CampaignPlannerSavesController::class . "@show");
    Route::   put("actors/{actor}/campaign-planner-saves/{campaignPlannerSave}", CampaignPlannerSavesController::class . "@update");
    Route::delete("actors/{actor}/campaign-planner-saves/{campaignPlannerSave}", CampaignPlannerSavesController::class . "@destroy");

});
