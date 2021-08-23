<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.campaigns.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ActorsCampaignsController;
use Neo\Http\Controllers\CampaignsController;
use Neo\Http\Controllers\ReviewsController;
use Neo\Http\Controllers\SchedulesController;
use Neo\Models\Campaign;
use Neo\Models\Schedule;

Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {


    /*
   |----------------------------------------------------------------------
   | Campaigns
   |----------------------------------------------------------------------
   */

    Route::model("campaign", Campaign::class);

    Route::   get("campaigns", CampaignsController::class . "@index");
    Route::  post("campaigns", CampaignsController::class . "@store");

    Route::   get("campaigns/{campaign}", CampaignsController::class . "@show");
    Route::   put("campaigns/{campaign}", CampaignsController::class . "@update");
    Route::delete("campaigns/{campaign}", CampaignsController::class . "@destroy");

    /*
   |----------------------------------------------------------------------
   | Campaigns Locations
   |----------------------------------------------------------------------
   */

    Route::   put("campaigns/{campaign}/locations"           , CampaignsController::class . "@syncLocations");
    Route::delete("campaigns/{campaign}/locations/{location}", CampaignsController::class . "@removeLocation");

    /*
   |----------------------------------------------------------------------
   | Schedules
   |----------------------------------------------------------------------
   */

    Route::model("schedule", Schedule::class);

    Route::   get("schedules/pending"             , SchedulesController::class . "@pending");
    Route::   put("schedules/{schedule}"          , SchedulesController::class . "@update");
    Route::delete("schedules/{schedule}"          , SchedulesController::class . "@destroy");

    Route::  post("campaigns/{campaign}/reorder"  , SchedulesController::class . "@reorder");
    Route::  post("campaigns/{campaign}/schedules", SchedulesController::class . "@store");
    Route::  post("campaigns/{campaign}/insert"   , SchedulesController::class . "@insert");


    /*
    |----------------------------------------------------------------------
    | Reviews
    |----------------------------------------------------------------------
    */

    Route::post("schedules/{schedule}/reviews", ReviewsController::class . "@store");

    /*
    |----------------------------------------------------------------------
    | Actors' Campaigns
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/campaigns", ActorsCampaignsController::class . "@index");
});
