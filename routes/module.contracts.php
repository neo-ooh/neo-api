<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.contracts.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\AdvertiserRepresentationsController;
use Neo\Http\Controllers\AdvertisersController;
use Neo\Http\Controllers\AvailabilitiesController;
use Neo\Http\Controllers\ClientsController;
use Neo\Http\Controllers\ContractBurstsController;
use Neo\Http\Controllers\ContractsController;
use Neo\Http\Controllers\ContractsFlightsBroadSignExportController;
use Neo\Http\Controllers\ContractsFlightsReservationsController;
use Neo\Http\Controllers\ContractsScreenshotsController;
use Neo\Models\Advertiser;
use Neo\Models\Client;
use Neo\Models\Contract;
use Neo\Models\ContractBurst;
use Neo\Models\ContractFlight;
use Neo\Models\ContractScreenshot;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1",
], static function () {
    /*
    |----------------------------------------------------------------------
    | Advertisers
    |----------------------------------------------------------------------
    */

    Route::model("advertiser", Advertiser::class);

    Route::   get("advertisers", AdvertisersController::class . "@index");
    Route::   get("advertisers/_by_id", AdvertisersController::class . "@byId");
    Route::   get("advertisers/{advertiser}", AdvertisersController::class . "@show");
    Route::   put("advertisers/{advertiser}", AdvertisersController::class . "@update");

    Route::  post("advertisers/{advertiser}/representations", AdvertiserRepresentationsController::class . "@store");
    Route::   put("advertisers/{advertiser}/representations/{broadcaster}", AdvertiserRepresentationsController::class . "@update");
    Route::delete("advertisers/{advertiser}/representations/{broadcaster}", AdvertiserRepresentationsController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Clients
    |----------------------------------------------------------------------
    */

    Route::model("client", Client::class);

    Route::get("clients", ClientsController::class . "@index");
    Route::get("clients/_by_id", ClientsController::class . "@byId");
    Route::get("clients/{client}", ClientsController::class . "@show");


    /*
    |----------------------------------------------------------------------
    | Contracts
    |----------------------------------------------------------------------
    */

    Route::model("contract", Contract::class);

    Route::   get("contracts", ContractsController::class . "@index");
    Route::  post("contracts", ContractsController::class . "@store");
    Route::   get("contracts/_recent", ContractsController::class . "@recent");
    Route::   get("contracts/{contract}", ContractsController::class . "@show");
    Route::   put("contracts/{contract}", ContractsController::class . "@update");
    Route::delete("contracts/{contract}", ContractsController::class . "@destroy");
    Route::  post("contracts/{contract}/_refresh", ContractsController::class . "@refresh");

    /*
    |----------------------------------------------------------------------
    | Contracts flights
    |----------------------------------------------------------------------
    */

    Route::model("flight", ContractFlight::class);

    Route::   get("contracts/{contract}/flights/{flight}/broadsign-exports", ContractsFlightsBroadSignExportController::class . "@index");
    Route::   get("contracts/{contract}/flights/{flight}/broadsign-exports/{network}", ContractsFlightsBroadSignExportController::class . "@show");
    Route::   put("contracts/{contract}/flights/{flight}/reservations/_sync", ContractsFlightsReservationsController::class . "@sync");


    /*
    |----------------------------------------------------------------------
    | Bursts
    |----------------------------------------------------------------------
    */

    Route::model("burst", ContractBurst::class);

    Route::  post("bursts", ContractBurstsController::class . "@store");
    Route::   get("bursts/{burst}", ContractBurstsController::class . "@show");
    Route::delete("bursts/{burst}", ContractBurstsController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Bursts Screenshots
    |----------------------------------------------------------------------
    */

    Route::model("screenshot", ContractScreenshot::class);

    Route::delete("screenshots/{screenshot}", ContractsScreenshotsController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Availabilities
    |----------------------------------------------------------------------
    */

    Route::post("availabilities", AvailabilitiesController::class . "@index");

});
