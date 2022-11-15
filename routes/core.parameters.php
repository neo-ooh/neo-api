<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.parameters.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ParametersController;
use Neo\Models\Parameter;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1",
], static function () {
    Route::model("parameter", Parameter::class);

    Route::  get("parameters", ParametersController::class . "@index");
    Route::  get("parameters/{parameter:slug}", ParametersController::class . "@show");
    Route:: post("parameters/{parameter:slug}", ParametersController::class . "@update");
    Route::  put("parameters/{parameter:slug}", ParametersController::class . "@update");
});
