<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - news.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Dynamics\Http\Controllers\NewsRecordsCategoriesController;
use Neo\Modules\Dynamics\Http\Controllers\NewsRecordsController;
use Neo\Modules\Dynamics\Models\NewsRecord;

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


Route::group([
	             "middleware" => "default",
	             "prefix"     => "v1",
             ],
	static function () {
		/*
		|----------------------------------------------------------------------
		| News Categories
		|----------------------------------------------------------------------
		*/

		Route::   get("dynamics/news/categories/", [NewsRecordsCategoriesController::class, "index"]);

		/*
		|----------------------------------------------------------------------
		| News Records
		|----------------------------------------------------------------------
		*/

		Route::model("newsRecord", NewsRecord::class);
		Route::  get("dynamics/news/records", [NewsRecordsController::class, "index"]);

	});
