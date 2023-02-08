<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.odoo.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\Odoo\ContractsController;
use Neo\Modules\Properties\Http\Controllers\Odoo\PropertiesController;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1/odoo",
             ], function () {
    /*
    |----------------------------------------------------------------------
    | Properties
    |----------------------------------------------------------------------
    */

    Route::post("properties", PropertiesController::class . "@store");
    Route::delete("properties/{property}", PropertiesController::class . "@destroy");

    /*
    |----------------------------------------------------------------------
    | Contracts
    |----------------------------------------------------------------------
    */

    Route::get("contracts/{contractName}", ContractsController::class . "@show");
    Route::post("contracts/{contractName}", ContractsController::class . "@send");
});
