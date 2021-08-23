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
use Neo\Http\Controllers\ParamsController;
use Neo\Models\Param;

Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {
    Route::model("parameter", Param::class);

    Route::  get("params/{parameter:slug}", ParamsController::class . "@show");
    Route:: post("params/{parameter:slug}", ParamsController::class . "@update");
    Route::  put("params/{parameter:slug}", ParamsController::class . "@update");
});
