<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.broadsign.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ContractBurstsController;
use Neo\Models\BroadSignCriteria;
use Neo\Models\BroadSignSeparation;
use Neo\Models\BroadSignTrigger;
use Neo\Models\ContractBurst;


Route::group([
    "middleware" => "broadsign",
    "prefix"     => "v1/broadsign"
], function () {
    /*
    |--------------------------------------------------------------------------
    | Bursts
    |--------------------------------------------------------------------------
    */

    Route::model("burst", ContractBurst::class);

    Route::post("burst_callback/{burst}", ContractBurstsController::class . "@receive");
});
