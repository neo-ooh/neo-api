<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - properties.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\GroupTrafficStatsController;
use Neo\Modules\Properties\Http\Controllers\MonthlyTrafficController;
use Neo\Modules\Properties\Http\Controllers\OpeningHoursController;
use Neo\Modules\Properties\Http\Controllers\PropertiesContactsController;
use Neo\Modules\Properties\Http\Controllers\PropertiesController;
use Neo\Modules\Properties\Http\Controllers\PropertiesStatisticsController;
use Neo\Modules\Properties\Http\Controllers\PropertiesTrafficController;
use Neo\Modules\Properties\Http\Controllers\PropertiesTranslationsController;
use Neo\Modules\Properties\Http\Controllers\PropertyPicturesController;
use Neo\Modules\Properties\Http\Controllers\TrafficSnapshotsController;
use Neo\Modules\Properties\Http\Controllers\TrafficSourcesController;
use Neo\Modules\Properties\Models\TrafficSource;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ],
    static function () {
        /*
        |----------------------------------------------------------------------
        | Properties
        |----------------------------------------------------------------------
        */

        Route::   get("properties", PropertiesController::class . "@index");
        Route::   get("properties/_by_id", PropertiesController::class . "@byId");
        Route::  post("properties", PropertiesController::class . "@store");
        Route::   get("properties/_networkDump", PropertiesController::class . "@networkDump");
        Route::   get("properties/_need_attention", PropertiesController::class . "@needAttention");
        Route::   get("properties/_search", PropertiesController::class . "@search");
        Route::   get("properties/{propertyId}", PropertiesController::class . "@show")->whereNumber("propertyId");
        Route::   put("properties/{property}", PropertiesController::class . "@update");
        Route::   put("properties/{property}/_mark_reviewed", PropertiesController::class . "@markReviewed");
        Route::   put("properties/{property}/address", PropertiesController::class . "@updateAddress");
        Route::delete("properties/{property}", PropertiesController::class . "@destroy");

        Route::   get("properties/{property}/_dump", PropertiesController::class . "@dump");

        Route::   get("actors/{actor}/traffic-statistics", GroupTrafficStatsController::class . "@show");


        /*
        |----------------------------------------------------------------------
        | Properties Translation
        |----------------------------------------------------------------------
        */

        Route::   put("properties/{property}/translations/{locale}", PropertiesTranslationsController::class . "@update");


        /*
        |----------------------------------------------------------------------
        | Properties Traffic
        |----------------------------------------------------------------------
        */

        Route::   get("properties/{property}/traffic", [PropertiesTrafficController::class, "index"]);
        Route::   put("properties/{property}/traffic", [PropertiesTrafficController::class, "update"]);

        Route::  post("properties/{property}/monthly_traffic", [MonthlyTrafficController::class, "store"]);

        Route::  get("properties/{property}/statistics", PropertiesStatisticsController::class . "@show");

        /*
        |----------------------------------------------------------------------
        | Traffic Snapshot
        |----------------------------------------------------------------------
        */

        Route::  post("traffic/_refresh_snapshot", TrafficSnapshotsController::class . "@refresh");

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
        | Properties Pictures
        |----------------------------------------------------------------------
        */

        Route::   get("properties/{property}/pictures", PropertyPicturesController::class . "@index");
        Route::  post("properties/{property}/pictures", PropertyPicturesController::class . "@store");
        Route::   put("properties/{property}/pictures/{propertyPicture}", PropertyPicturesController::class . "@update");
        Route::delete("properties/{property}/pictures/{propertyPicture}", PropertyPicturesController::class . "@destroy");


        /*
        |----------------------------------------------------------------------
        | Opening Hours
        |----------------------------------------------------------------------
        */

        Route::   get("properties/{property}/contacts", PropertiesContactsController::class . "@show");
        Route::  post("properties/{property}/contacts", PropertiesContactsController::class . "@store");
        Route::   put("properties/{property}/contacts/{user}", PropertiesContactsController::class . "@update");
        Route::delete("properties/{property}/contacts/{user}", PropertiesContactsController::class . "@destroy");


        /*
        |----------------------------------------------------------------------
        | Opening Hours
        |----------------------------------------------------------------------
        */

        Route::  post("properties/{property}/opening-hours/_refresh", OpeningHoursController::class . "@refresh");
        Route::   put("properties/{property}/opening-hours/{weekday}", OpeningHoursController::class . "@update");
    });
