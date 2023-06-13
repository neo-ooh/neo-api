<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - broadcast-resources.php
 */

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

use Illuminate\Support\Facades\Route;
use Neo\Modules\Broadcast\Http\Controllers\BroadcastJobsController;
use Neo\Modules\Broadcast\Http\Controllers\BroadcastResourcesController;
use Neo\Modules\Broadcast\Http\Controllers\ExternalResourcesController;
use Neo\Modules\Broadcast\Models\BroadcastJob;
use Neo\Modules\Broadcast\Models\BroadcastResource;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ], static function () {
    /*
    |----------------------------------------------------------------------
    | Broadcast Resources
    |----------------------------------------------------------------------
    */

    Route::model("broadcastResource", BroadcastResource::class);

    Route::   get("broadcast-resources/{broadcastResource}", BroadcastResourcesController::class . "@show");
    Route::   get("broadcast-resources/{broadcastResource}/representations", BroadcastResourcesController::class . "@representations");
    Route::   get("broadcast-resources/{broadcastResource}/jobs", BroadcastResourcesController::class . "@jobs");

    Route::   get("broadcast-resources/{broadcastResource}/performances", BroadcastResourcesController::class . "@performances");

    Route::  post("broadcast-resources/{broadcastResource}/_promote", BroadcastResourcesController::class . "@promote");

    /*
    |----------------------------------------------------------------------
    | Broadcast Jobs
    |----------------------------------------------------------------------
    */

    Route::model("broadcastJob", BroadcastJob::class);

    Route::   get("broadcast-jobs", [BroadcastJobsController::class, "index"]);
    Route::   put("broadcast-jobs/{broadcastJob}/_cancel", [BroadcastJobsController::class, "cancel"]);
    Route::   put("broadcast-jobs/{broadcastJob}/_retry", [BroadcastJobsController::class, "retry"]);


    /*
    |----------------------------------------------------------------------
    | External resources
    |----------------------------------------------------------------------
    */

    Route::delete("external-resources/{externalResource}", ExternalResourcesController::class . "@destroy");

});
