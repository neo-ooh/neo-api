<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.odoo.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\Odoo\ContractsController;

Route::group([
                 "middleware" => "default",
                 "prefix"     => "v1/odoo",
             ], function () {
    /*
    |----------------------------------------------------------------------
    | Contracts
    |----------------------------------------------------------------------
    */

    Route::get("contracts/{contractName}", ContractsController::class . "@show");
    Route::post("contracts/{contractName}", ContractsController::class . "@send");
});
