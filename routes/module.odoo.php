<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.misc.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ModulesController;
use Neo\Http\Controllers\Odoo\PropertiesController;
use Neo\Http\Controllers\StatsController;

Route::group([
    "middleware" => "default",
    "prefix" => "v1/odoo"
], function () {
    /*
    |----------------------------------------------------------------------
    | Properties
    |----------------------------------------------------------------------
    */

    Route::post("properties", PropertiesController::class . "@store");
    Route::delete("properties/{property}", PropertiesController::class . "@destroy");

});