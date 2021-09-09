<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.networks.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ActorsLocationsController;
use Neo\Http\Controllers\BroadcasterConnectionsController;
use Neo\Http\Controllers\ContainersController;
use Neo\Http\Controllers\DisplayTypesController;
use Neo\Http\Controllers\LocationsController;
use Neo\Http\Controllers\LocationsPlayersController;
use Neo\Http\Controllers\NetworksController;
use Neo\Models\BroadcasterConnection;
use Neo\Models\DisplayType;
use Neo\Models\Location;
use Neo\Models\Network;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1"
], function () {
    /*
    |----------------------------------------------------------------------
    | Broadcasters Connections
    |----------------------------------------------------------------------
    */

    Route::model("connection", BroadcasterConnection::class);

    Route::   get("broadcasters/", BroadcasterConnectionsController::class . "@index");
    Route::  post("broadcasters/", BroadcasterConnectionsController::class . "@store");
    Route::   get("broadcasters/{connection}", BroadcasterConnectionsController::class . "@show");
    Route::  post("broadcasters/{connection}", BroadcasterConnectionsController::class . "@update");
    Route::delete("broadcasters/{connection}", BroadcasterConnectionsController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Networks
    |----------------------------------------------------------------------
    */

    Route::model("network", Network::class);

    Route::   get("networks", NetworksController::class . "@index");
    Route::  post("networks", NetworksController::class . "@store");
    Route::  post("networks/_refresh", NetworksController::class . "@refresh");
    Route::   get("networks/{network}", NetworksController::class . "@show");
    Route::   put("networks/{network}", NetworksController::class . "@update");
    Route::delete("networks/{network}", NetworksController::class . "@destroy");

    Route::get("networks/{network}/display-types", DisplayTypesController::class . "@byNetwork");


    /*
    |----------------------------------------------------------------------
    | Display Types
    |----------------------------------------------------------------------
    */

    Route::model("displayType", DisplayType::class);

    Route::get("display-types", DisplayTypesController::class . "@index");
    Route::put("display-types/{displayType}", DisplayTypesController::class . "@update");


    /*
    |----------------------------------------------------------------------
    | Locations
    |----------------------------------------------------------------------
    */

    Route::model("location", Location::class);

    Route::get("locations", LocationsController::class . "@index");
    Route::get("locations/_search", LocationsController::class . "@search");
    Route::get("locations/_network", LocationsController::class . "@allByNetwork");
    Route::get("locations/{location}", LocationsController::class . "@show");
    Route::put("locations/{location}", LocationsController::class . "@update");

    Route::get("locations/{location}/players", LocationsPlayersController::class . "@index");
    Route::get("locations/{location}/screens_state", LocationsController::class . "@setScreenState");

    /*
    |----------------------------------------------------------------------
    | Containers
    |----------------------------------------------------------------------
    */

    Route::get("containers", ContainersController::class . "@index");

    /*
    |----------------------------------------------------------------------
    | Actors Locations
    |----------------------------------------------------------------------
    */

    Route::get("actors/{actor}/locations", ActorsLocationsController::class . "@index");
    Route::put("actors/{actor}/locations", ActorsLocationsController::class . "@sync");
});
