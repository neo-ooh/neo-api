<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
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
use Neo\Http\Controllers\ContractFlightsController;
use Neo\Http\Controllers\ContractsController;
use Neo\Http\Controllers\ContractsFlightsExportController;
use Neo\Http\Controllers\ContractsFlightsReservationsController;
use Neo\Http\Controllers\ContractsScreenshotsController;
use Neo\Http\Controllers\ScreenshotsController;
use Neo\Http\Controllers\ScreenshotsRequestsController;
use Neo\Models\Advertiser;
use Neo\Models\Client;
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Models\Screenshot;
use Neo\Models\ScreenshotRequest;
use Neo\Modules\Properties\Http\Controllers\ProofOfPerformancesController;

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

	Route::get("clients", [ClientsController::class, "index"]);
	Route::get("clients/_by_id", [ClientsController::class, "byId"]);
	Route::get("clients/{client}", [ClientsController::class, "show"]);


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


	Route::   get("flights", [ContractFlightsController::class, "index"]);
	Route::   get("flights/{flight}", [ContractFlightsController::class, "show"]);

	Route::   get("flights/{flight}/exports", ContractsFlightsExportController::class . "@index");
	Route::   get("flights/{flight}/exports/{network}", ContractsFlightsExportController::class . "@show");

	Route::   put("flights/{flight}/reservations/_sync", ContractsFlightsReservationsController::class . "@sync");


	/*
	|----------------------------------------------------------------------
	| Screenshots Requests
	|----------------------------------------------------------------------
	*/

	Route::model("screenshotRequest", ScreenshotRequest::class);

	Route::   get("screenshots-requests", [ScreenshotsRequestsController::class, "index"]);
	Route::  post("screenshots-requests", [ScreenshotsRequestsController::class, "store"]);
	Route::   get("screenshots-requests/{screenshotRequest}", [ScreenshotsRequestsController::class, "show"]);
	Route::delete("screenshots-requests/{screenshotRequest}", [ScreenshotsRequestsController::class, "destroy"]);


	/*
	|----------------------------------------------------------------------
	| Screenshots
	|----------------------------------------------------------------------
	*/

	Route::model("screenshot", Screenshot::class);

	Route::   get("screenshots", [ScreenshotsController::class, "index"]);

	Route::  post("contracts/{contract}/screenshots/{screenshot}", [ContractsScreenshotsController::class, "associate"]);
	Route::delete("contracts/{contract}/screenshots/{screenshot}", [ContractsScreenshotsController::class, "dissociate"]);

	/*
	|----------------------------------------------------------------------
	| Availabilities
	|----------------------------------------------------------------------
	*/

	Route::post("availabilities", [AvailabilitiesController::class, "index"]);
	Route::post("availabilities/_year", [AvailabilitiesController::class, "show"]);

	/*
	|----------------------------------------------------------------------
	| POP
	|----------------------------------------------------------------------
	*/

	Route::   get("contracts/{contract}/proof-of-performances", [ProofOfPerformancesController::class, "getBase"]);
	Route::  post("contracts/{contract}/proof-of-performances", [ProofOfPerformancesController::class, "build"]);

});
