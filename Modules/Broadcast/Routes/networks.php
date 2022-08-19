<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - networks.php
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

use Neo\Http\Controllers\PropertiesController;
use Neo\Modules\Broadcast\Http\Controllers\BroadcasterConnectionsController;
use Neo\Modules\Broadcast\Http\Controllers\DisplayTypesController;
use Neo\Modules\Broadcast\Http\Controllers\LocationsController;
use Neo\Modules\Broadcast\Http\Controllers\LocationsPlayersController;
use Neo\Modules\Broadcast\Http\Controllers\NetworkContainersController;
use Neo\Modules\Broadcast\Http\Controllers\NetworksController;
use Neo\Modules\Broadcast\Models\BroadcasterConnection;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Models\Network;

Route::group([
    "middleware" => "default",
    "prefix"     => "v2"
], static function () {
    /*
    |----------------------------------------------------------------------
    | Broadcasters Connections
    |----------------------------------------------------------------------
    */

    Route::model("connection", BroadcasterConnection::class);

    Route::   get("broadcasters/", BroadcasterConnectionsController::class . "@index");
    Route::   get("broadcasters/_by_id", BroadcasterConnectionsController::class . "@byId");
    Route::  post("broadcasters/", BroadcasterConnectionsController::class . "@store");
    Route::   get("broadcasters/{connection}", BroadcasterConnectionsController::class . "@show");
    Route::   put("broadcasters/{connection}", BroadcasterConnectionsController::class . "@update");
    Route::delete("broadcasters/{connection}", BroadcasterConnectionsController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Networks
    |----------------------------------------------------------------------
    */

    Route::model("network", Network::class);
    Route::   get("networks", NetworksController::class . "@index");
    Route::  post("networks", NetworksController::class . "@store");
    Route::   get("networks/{network}", NetworksController::class . "@show");
    Route::   put("networks/{network}", NetworksController::class . "@update");
    Route::delete("networks/{network}", NetworksController::class . "@destroy");
    Route::  post("networks/{network}/_synchronize", NetworksController::class . "@synchronize");

    Route::get("networks/{network}/_dump_properties", PropertiesController::class . "@dumpNetwork");

    Route::get("networks/{network}/containers", NetworkContainersController::class . "@index");


    /*
    |----------------------------------------------------------------------
    | Display Types
    |----------------------------------------------------------------------
    */

    Route::    get("display-types", DisplayTypesController::class . "@index");

    /*
    |----------------------------------------------------------------------
    | Locations
    |----------------------------------------------------------------------
    */

    Route::model("location", Location::class);

    Route::get("locations", LocationsController::class . "@index");
    Route::get("locations/_search", LocationsController::class . "@search");
    Route::get("locations/{location}", LocationsController::class . "@show");
    Route::put("locations/{location}", LocationsController::class . "@update");

    Route::get("locations/{location}/players", LocationsPlayersController::class . "@index");
    Route::get("locations/{location}/screens_state", LocationsController::class . "@setScreenState");
    Route::put("locations/{location}/_force_refresh", LocationsController::class . "@_forceRefreshPlaylist");
});
