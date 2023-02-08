<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - fields.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\FieldsCategoriesController;
use Neo\Modules\Properties\Http\Controllers\FieldsController;
use Neo\Modules\Properties\Http\Controllers\FieldSegmentsController;
use Neo\Modules\Properties\Http\Controllers\PropertiesFieldsSegmentsController;
use Neo\Modules\Properties\Models\Field;
use Neo\Modules\Properties\Models\FieldsCategory;
use Neo\Modules\Properties\Models\FieldSegment;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ],
    static function () {
        /*
        |----------------------------------------------------------------------
        | Fields
        |----------------------------------------------------------------------
        */

        Route::model("fieldsCategory", FieldsCategory::class);
        Route::model("field", Field::class);
        Route::model("segment", FieldSegment::class);

        Route::   get("fields-categories", FieldsCategoriesController::class . "@index");
        Route::  post("fields-categories", FieldsCategoriesController::class . "@store");
        Route::   get("fields-categories/_by_id", FieldsCategoriesController::class . "@byId");
        Route::   put("fields-categories/{fieldsCategory}", FieldsCategoriesController::class . "@update");
        Route::   put("fields-categories/{fieldsCategory}/_reorder", FieldsCategoriesController::class . "@reorder");
        Route::delete("fields-categories/{fieldsCategory}", FieldsCategoriesController::class . "@destroy");

        Route::   get("fields", FieldsController::class . "@index");
        Route::  post("fields", FieldsController::class . "@store");
        Route::   get("fields/{field}", FieldsController::class . "@show");
        Route::   put("fields/{field}", FieldsController::class . "@update");
        Route::delete("fields/{field}", FieldsController::class . "@destroy");

        Route::  post("fields/{field}/segments", FieldSegmentsController::class . "@store");
        Route::   put("fields/{field}/segments/{segment}", FieldSegmentsController::class . "@update");
        Route::delete("fields/{field}/segments/{segment}", FieldSegmentsController::class . "@destroy");

        Route::  post("properties/{property}/fields/{field}", PropertiesFieldsSegmentsController::class . "@store");
        Route::delete("properties/{property}/fields/{field}", PropertiesFieldsSegmentsController::class . "@destroy");
    });
