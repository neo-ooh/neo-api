<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - broadcast-tags.php
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
use Neo\Modules\Broadcast\Http\Controllers\BroadcastTagsController;
use Neo\Modules\Broadcast\Http\Controllers\BroadcastTagsExternalRepresentationsController;
use Neo\Modules\Broadcast\Models\BroadcastTag;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ], static function () {
    /*
    |----------------------------------------------------------------------
    | Broadcast Tags
    |----------------------------------------------------------------------
    */

    Route::model("broadcastTag", BroadcastTag::class);

    Route::   get("broadcast-tags/", BroadcastTagsController::class . "@index");
    Route::  post("broadcast-tags/", BroadcastTagsController::class . "@store");
    Route::   get("broadcast-tags/_by_id", BroadcastTagsController::class . "@by_id");
    Route::   get("broadcast-tags/{broadcastTag}", BroadcastTagsController::class . "@show");
    Route::   put("broadcast-tags/{broadcastTag}", BroadcastTagsController::class . "@update");
    Route::delete("broadcast-tags/{broadcastTag}", BroadcastTagsController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Broadcast Tags External Representations
    |----------------------------------------------------------------------
    */

    Route::get("broadcast-tags/{broadcastTag}/representations", BroadcastTagsExternalRepresentationsController::class . "@index");
    Route::put("broadcast-tags/{broadcastTag}/representations/_sync", BroadcastTagsExternalRepresentationsController::class . "@update");
});
