<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.properties.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\CitiesController;
use Neo\Http\Controllers\CountriesController;
use Neo\Http\Controllers\MarketsController;
use Neo\Http\Controllers\PropertiesController;
use Neo\Http\Controllers\PropertiesTrafficController;
use Neo\Http\Controllers\ProvincesController;
use Neo\Http\Controllers\TrafficSourcesController;
use Neo\Models\Property;
use Neo\Models\TrafficSource;

Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {
    /*
    |----------------------------------------------------------------------
    | Properties
    |----------------------------------------------------------------------
    */

    Route::model("property", Property::class);

    Route::  post("properties"                   , PropertiesController::class . "@store");
    Route::   get("properties/{propertyId}"      , PropertiesController::class . "@show");
    Route::   put("properties/{property}"        , PropertiesController::class . "@update");
    Route::   put("properties/{property}/address", PropertiesController::class . "@updateAddress");
    Route::delete("properties/{property}"        , PropertiesController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Properties Traffic
    |----------------------------------------------------------------------
    */

    Route::   get("properties/{property}/traffic", PropertiesTrafficController::class . "@index");
    Route::   put("properties/{property}/traffic", PropertiesTrafficController::class . "@update");
    Route::  post("properties/{property}/traffic", PropertiesTrafficController::class . "@store");

    /*
    |----------------------------------------------------------------------
    | Traffic Sources
    |----------------------------------------------------------------------
    */

    Route::model("trafficSource", TrafficSource::class);

    Route::get("traffic-sources"                   , TrafficSourcesController::class . "@index");
    Route::post("traffic-sources"                  , TrafficSourcesController::class . "@store");
    Route::put("traffic-sources/{trafficSource}"   , TrafficSourcesController::class . "@update");
    Route::delete("traffic-sources/{trafficSource}", TrafficSourcesController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Addresses
    |----------------------------------------------------------------------
    */

    Route::   get("countries"          , CountriesController::class . "@index");
    Route::   get("countries/{country}", CountriesController::class . "@show");

    // Provinces
    Route::   get("countries/{country}/provinces"           , ProvincesController::class . "@index");
    Route::   get("countries/{country}/provinces/{province}", ProvincesController::class . "@show");

    // Markets
    Route::   put("markets/{market}"                                , MarketsController::class . "@update");
    Route::  post("countries/{country}/provinces/{province}/markets", MarketsController::class . "@store");
    Route::delete("markets/{market}"                                , MarketsController::class . "@destroy");

    // Cities
    Route::   get("countries/{country}/provinces/{province}/cities"       , CitiesController::class . "@index");
    Route::  post("countries/{country}/provinces/{province}/cities"       , CitiesController::class . "@store");
    Route::   put("countries/{country}/provinces/{province}/cities/{city}", CitiesController::class . "@update");
    Route::delete("countries/{country}/provinces/{province}/cities/{city}", CitiesController::class . "@destroy");
});
