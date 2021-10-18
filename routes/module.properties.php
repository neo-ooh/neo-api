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
use Neo\Http\Controllers\FieldsController;
use Neo\Http\Controllers\FieldSegmentsController;
use Neo\Http\Controllers\MarketsController;
use Neo\Http\Controllers\NetworkFieldsController;
use Neo\Http\Controllers\PropertiesController;
use Neo\Http\Controllers\PropertiesDataController;
use Neo\Http\Controllers\PropertiesStatisticsController;
use Neo\Http\Controllers\PropertiesTrafficController;
use Neo\Http\Controllers\PropertyPicturesController;
use Neo\Http\Controllers\ProvincesController;
use Neo\Http\Controllers\TrafficSourcesController;
use Neo\Models\Field;
use Neo\Models\FieldSegment;
use Neo\Models\Library;
use Neo\Models\TrafficSource;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1"
], function () {
    /*
    |----------------------------------------------------------------------
    | Properties
    |----------------------------------------------------------------------
    */

    Route::   get("properties", PropertiesController::class . "@index");
    Route::  post("properties", PropertiesController::class . "@store");
    Route::   get("properties/{propertyId}", PropertiesController::class . "@show");
    Route::   put("properties/{property}", PropertiesController::class . "@update");
    Route::   put("properties/{property}/address", PropertiesController::class . "@updateAddress");
    Route::delete("properties/{property}", PropertiesController::class . "@destroy");


    /*
    |----------------------------------------------------------------------
    | Properties Traffic
    |----------------------------------------------------------------------
    */

    Route::   get("properties/{property}/traffic", PropertiesTrafficController::class . "@index");
    Route::   put("properties/{property}/traffic", PropertiesTrafficController::class . "@update");
    Route::  post("properties/{property}/traffic", PropertiesTrafficController::class . "@store");


    Route::  get("properties/{property}/statistics", PropertiesStatisticsController::class . "@show");

    /*
    |----------------------------------------------------------------------
    | Properties Data
    |----------------------------------------------------------------------
    */

    Route::   put("properties/{property}/data", PropertiesDataController::class . "@update");

    /*
    |----------------------------------------------------------------------
    | Properties Pictures
    |----------------------------------------------------------------------
    */

    Route::   get("properties/{property}/pictures", PropertyPicturesController::class . "@index");
    Route::  post("properties/{property}/pictures", PropertyPicturesController::class . "@store");
    Route::   put("properties/{property}/pictures/{propertyPicture}", PropertyPicturesController::class . "@update");
    Route::delete("properties/{property}/pictures/{propertyPicture}", PropertyPicturesController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Traffic Sources
    |----------------------------------------------------------------------
    */

    Route::model("trafficSource", TrafficSource::class);

    Route::   get("traffic-sources", TrafficSourcesController::class . "@index");
    Route::  post("traffic-sources", TrafficSourcesController::class . "@store");
    Route::   put("traffic-sources/{trafficSource}", TrafficSourcesController::class . "@update");
    Route::delete("traffic-sources/{trafficSource}", TrafficSourcesController::class . "@destroy");

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
    Route::   put("markets/{market}", MarketsController::class . "@update");
    Route::  post("countries/{country}/provinces/{province}/markets", MarketsController::class . "@store");
    Route::delete("markets/{market}", MarketsController::class . "@destroy");

    // Cities
    Route::   get("countries/{country}/provinces/{province}/cities", CitiesController::class . "@index");
    Route::  post("countries/{country}/provinces/{province}/cities", CitiesController::class . "@store");
    Route::   put("countries/{country}/provinces/{province}/cities/{city}", CitiesController::class . "@update");
    Route::delete("countries/{country}/provinces/{province}/cities/{city}", CitiesController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Fields
    |----------------------------------------------------------------------
    */

    Route::model("field", Field::class);
    Route::model("fieldSegment", FieldSegment::class);

    Route::   get("fields", FieldsController::class . "@index");
    Route::  post("fields", FieldsController::class . "@store");
    Route::   get("fields/{field}", FieldsController::class . "@show");
    Route::   put("fields/{field}", FieldsController::class . "@update");
    Route::delete("fields/{field}", FieldsController::class . "@destroy");

    Route::  post("fields/{field}/segments", FieldSegmentsController::class . "@store");
    Route::   put("fields/{field}/segments/{fieldSegment}", FieldSegmentsController::class . "@update");
    Route::delete("fields/{field}/segments/{fieldSegment}", FieldSegmentsController::class . "@destroy");

    Route::   get("networks/{network}/segments", NetworkFieldsController::class . "@index");
    Route::   put("networks/{network}/segments", NetworkFieldsController::class . "@update");
});
