<?php

use Neo\Modules\Broadcast\Http\Controllers\CampaignsController;
use Neo\Modules\Broadcast\Http\Controllers\CampaignsLocationsController;
use Neo\Modules\Broadcast\Http\Controllers\CampaignsSchedulesController;
use Neo\Modules\Broadcast\Http\Controllers\ExternalResourcesController;
use Neo\Modules\Broadcast\Http\Controllers\ScheduleContentsController;
use Neo\Modules\Broadcast\Http\Controllers\SchedulesController;
use Neo\Modules\Broadcast\Http\Controllers\SchedulesReviewsController;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\ScheduleContent;

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
    "prefix"     => "v2",
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

    Route::   get("campaigns/{campaign}/locations", CampaignsLocationsController::class . "@index");
    Route::   put("campaigns/{campaign}/locations", CampaignsLocationsController::class . "@sync");
    Route::delete("campaigns/{campaign}/locations/{location}", CampaignsLocationsController::class . "@remove");

    /*
   |----------------------------------------------------------------------
   | Schedules
   |----------------------------------------------------------------------
   */

    Route::model("schedule", Schedule::class);
    Route::model("scheduleContent", ScheduleContent::class);

    Route::  post("schedules", SchedulesController::class . "@store");
    Route::   get("schedules/_by_id", SchedulesController::class . "@byIds");
    Route::   get("schedules/_pending", SchedulesController::class . "@pending");
    Route::   get("schedules/{schedule}", SchedulesController::class . "@show")->withTrashed();
    Route::   put("schedules/{schedule}", SchedulesController::class . "@update");
    Route::delete("schedules/{schedule}", SchedulesController::class . "@destroy");

    Route::  post("schedules/{schedule}/contents", ScheduleContentsController::class . "@store");
    Route::   put("schedules/{schedule}/contents/{scheduleContent}", ScheduleContentsController::class . "@update");
    Route::delete("schedules/{schedule}/contents/{scheduleContent}", ScheduleContentsController::class . "@remove");

    Route::   get("campaigns/{campaign}/schedules", CampaignsSchedulesController::class . "@index");
    Route::   get("campaigns/{campaign}/expired-schedules", CampaignsSchedulesController::class . "@indexExpired");
    Route::  post("campaigns/{campaign}/schedules", CampaignsSchedulesController::class . "@store");
    Route::   put("campaigns/{campaign}/schedules/{schedule}", SchedulesController::class . "@updateWithCampaign");
    Route::  post("campaigns/{campaign}/schedules/_reorder", CampaignsSchedulesController::class . "@reorder");
    Route::delete("campaigns/{campaign}/schedules/{schedule}", SchedulesController::class . "@destroyWithCampaign");

    /*
    |----------------------------------------------------------------------
    | Reviews
    |----------------------------------------------------------------------
    */

    Route::get("schedules/{schedule}/reviews", SchedulesReviewsController::class . "@index");
    Route::post("schedules/{schedule}/reviews", SchedulesReviewsController::class . "@store");


    /*
    |----------------------------------------------------------------------
    | Campaigns
    |----------------------------------------------------------------------
    */

    Route::delete("external-resources/{externalResource}", ExternalResourcesController::class . "@destroy");
});

