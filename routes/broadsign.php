<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - broadsign.php
 */


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ContractBurstsController;
use Neo\Models\ContractBurst;

Route::prefix("v1/broadsign")->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Bursts
    |--------------------------------------------------------------------------
    */

    Route::model("burst", ContractBurst::class);

    Route::post("burst_callback/{burst}", ContractBurstsController::class . "@receive")->name('broadsign.bursts.receive');
});
