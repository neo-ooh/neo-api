<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.address.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\CitiesController;
use Neo\Http\Controllers\CountriesController;
use Neo\Http\Controllers\MarketsController;
use Neo\Http\Controllers\ProvincesController;

Route::group([
	             "middleware" => "default",
	             "prefix"     => "v1",
             ],
	static function () {
		/*
		|----------------------------------------------------------------------
		| Addresses
		|----------------------------------------------------------------------
		*/

		Route::   get("countries", CountriesController::class . "@index");
		Route::   get("countries/{country}", CountriesController::class . "@show");

		// Provinces
		Route::   get("countries/{country}/provinces", ProvincesController::class . "@index");
		Route::   get("countries/{country}/provinces/{province}", ProvincesController::class . "@show");

		// Markets
		Route::  post("markets", [MarketsController::class, "store"]);
		Route::   get("markets/_by_id", [MarketsController::class, "byIds"]);
		Route::   put("markets/{market}", [MarketsController::class, "update"]);
		Route::delete("markets/{market}", [MarketsController::class, "destroy"]);

		// Cities
		Route::   get("cities", [CitiesController::class, "index"]);
		Route::  post("cities", [CitiesController::class, "store"]);
		Route::  get("cities/_by_id", [CitiesController::class, "byIds"]);
		Route::   put("cities/{city}", [CitiesController::class, "update"]);
		Route::delete("cities/{city}", [CitiesController::class, "destroy"]);
	});
