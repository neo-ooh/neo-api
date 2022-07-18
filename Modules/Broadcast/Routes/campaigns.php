<?php

use Neo\Http\Controllers\ActorsCampaignsController;
use Neo\Modules\Broadcast\Http\Controllers\CampaignsController;
use Neo\Modules\Broadcast\Http\Controllers\SchedulesController;
use Neo\Modules\Broadcast\Http\Controllers\SchedulesReviewsController;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Schedule;

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

Route::group([
    "middleware" => "default",
    "prefix"     => "v1"
], static function () {

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

    Route::   put("campaigns/{campaign}/locations", CampaignsController::class . "@syncLocations");
    Route::delete("campaigns/{campaign}/locations/{location}", CampaignsController::class . "@removeLocation");

    /*
   |----------------------------------------------------------------------
   | Schedules
   |----------------------------------------------------------------------
   */

    Route::model("schedule", Schedule::class);

    Route::   get("schedules/pending", SchedulesController::class . "@pending");
    Route::   put("schedules/{schedule}", SchedulesController::class . "@update");
    Route::delete("schedules/{schedule}", SchedulesController::class . "@destroy");

    Route::  post("campaigns/{campaign}/reorder", SchedulesController::class . "@reorder");
    Route::  post("campaigns/{campaign}/schedules", SchedulesController::class . "@store");
    Route::  post("campaigns/{campaign}/insert", SchedulesController::class . "@insert");


    /*
    |----------------------------------------------------------------------
    | Reviews
    |----------------------------------------------------------------------
    */

    Route::post("schedules/{schedule}/reviews", SchedulesReviewsController::class . "@store");

    /*
    |----------------------------------------------------------------------
    | Actors' Campaigns
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/campaigns", ActorsCampaignsController::class . "@index");
});
