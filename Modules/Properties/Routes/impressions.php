<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - impressions.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\ImpressionsController;
use Neo\Modules\Properties\Http\Controllers\ImpressionsModelsController;
use Neo\Modules\Properties\Models\ImpressionsModel;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ],
    static function () {
        /*
        |----------------------------------------------------------------------
        | Impressions Models
        |----------------------------------------------------------------------
        */

        Route::model("impressionsModel", ImpressionsModel::class);

        Route::   get("product-categories/{productCategory}/impressions-models",
                      ImpressionsModelsController::class . "@showProductCategory");
        Route::  post("product-categories/{productCategory}/impressions-models",
                      ImpressionsModelsController::class . "@storeProductCategory");
        Route::   put("product-categories/{productCategory}/impressions-models/{impressionsModel}",
                      ImpressionsModelsController::class . "@updateProductCategory");
        Route::delete("product-categories/{productCategory}/impressions-models/{impressionsModel}",
                      ImpressionsModelsController::class . "@destroyProductCategory");

        Route::   get("products/{product}/impressions-models",
                      ImpressionsModelsController::class . "@showProduct");
        Route::  post("products/{product}/impressions-models",
                      ImpressionsModelsController::class . "@storeProduct");
        Route::   put("products/{product}/impressions-models/{impressionsModel}",
                      ImpressionsModelsController::class . "@updateProduct");
        Route::delete("products/{product}/impressions-models/{impressionsModel}",
                      ImpressionsModelsController::class . "@destroyProduct");

        /*
        |----------------------------------------------------------------------
        | Impressions export
        |----------------------------------------------------------------------
        */

        Route::get("impressions/broadsign/{displayUnitId}", ImpressionsController::class . "@broadsignDisplayUnit")
             ->whereNumber('displayUnitId');
    });
