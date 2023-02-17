<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - unavailabilities.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\UnavailabilitiesController;
use Neo\Modules\Properties\Models\Unavailability;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1",
             ],
    static function () {
        /*
        |----------------------------------------------------------------------
        | Unavailabilities
        |----------------------------------------------------------------------
        */

        Route::model("unavailability", Unavailability::class);

        Route::  post("unavailabilities", [UnavailabilitiesController::class, "store"]);
        Route::   get("unavailabilities/{unavailability}", [UnavailabilitiesController::class, "show"]);
        Route::   put("unavailabilities/{unavailability}", [UnavailabilitiesController::class, "update"]);
        Route::delete("unavailabilities/{unavailability}", [UnavailabilitiesController::class, "destroy"]);
    });
