<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.demographics.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\DemographicValuesController;
use Neo\Http\Controllers\DemographicVariablesController;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ], static function () {
    Route::get("demographic_variables", DemographicVariablesController::class . "@index");
    Route::get("demographic_variables/_list_pipelines", DemographicValuesController::class . "@listPipelines");

    Route::get("demographic_values", DemographicValuesController::class . "@index");

    if (config("modules-legacy.properties.enabled")) {
        Route::post("properties/{property}/demographic_values", DemographicValuesController::class . "@store");
    }
});
