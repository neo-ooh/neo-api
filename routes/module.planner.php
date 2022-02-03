<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.planner.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\CampaignPlannerController;
use Neo\Http\Controllers\CampaignPlannerPolygonsController;
use Neo\Http\Controllers\CampaignPlannerSavesController;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1"
], function () {
    /*
    |----------------------------------------------------------------------
    | Saved Campaign Planner runs
    |----------------------------------------------------------------------
    */

    Route::   get("actors/{actor}/campaign-planner-saves", CampaignPlannerSavesController::class . "@index");
    Route::  post("actors/{actor}/campaign-planner-saves", CampaignPlannerSavesController::class . "@store");
    Route::   get("actors/{actor}/campaign-planner-saves/_recent", CampaignPlannerSavesController::class . "@recent");
    Route::   get("actors/{actor}/campaign-planner-saves/{campaignPlannerSave}", CampaignPlannerSavesController::class . "@show");
    Route::   put("actors/{actor}/campaign-planner-saves/{campaignPlannerSave}", CampaignPlannerSavesController::class . "@update");
    Route::  post("actors/{actor}/campaign-planner-saves/{campaignPlannerSave}/_share", CampaignPlannerSavesController::class . "@share");
    Route::delete("actors/{actor}/campaign-planner-saves/{campaignPlannerSave}", CampaignPlannerSavesController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Saved Campaign Planner runs
    |----------------------------------------------------------------------
    */

    Route::   get("actors/{actor}/campaign-planner-polygons", CampaignPlannerPolygonsController::class . "@index");
    Route::  post("actors/{actor}/campaign-planner-polygons", CampaignPlannerPolygonsController::class . "@store");
    Route::   get("actors/{actor}/campaign-planner-polygons/{campaignPlannerPolygon}", CampaignPlannerPolygonsController::class . "@show");
    Route::delete("actors/{actor}/campaign-planner-polygons/{campaignPlannerPolygon}", CampaignPlannerPolygonsController::class . "@destroy");

    Route::   get("campaign-planner/_data", CampaignPlannerController::class . "@data");
});


// Open two specific routes to guest to be able to display a planner instance when using a share link
Route::group([
    "middleware" => "guests",
    "prefix"     => "v1"
], function () {
    Route::   get("campaign-planner/{campaignPlannerSave}", CampaignPlannerController::class . "@saveAndDate");
});
