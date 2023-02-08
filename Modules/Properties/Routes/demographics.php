<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - demographics.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\DemographicValuesController;
use Neo\Modules\Properties\Http\Controllers\DemographicVariablesController;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ],
    static function () {
        Route::get("demographic_variables", DemographicVariablesController::class . "@index");
        Route::get("demographic_variables/_list_pipelines", DemographicValuesController::class . "@listPipelines");

        Route::get("demographic_values", DemographicValuesController::class . "@index");

        Route::post("properties/{property}/demographic_values", DemographicValuesController::class . "@store");
    });
