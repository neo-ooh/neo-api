<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - public.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Properties\Http\Controllers\Public\ProductsController;
use Neo\Modules\Properties\Http\Controllers\Public\PropertiesController;

Route::group([
	             "middleware" => "guests",
	             "prefix"     => "public/v1/",
             ],
	static function () {
		Route::get("properties", [PropertiesController::class, "index"]);
		Route::get("products", [ProductsController::class, "index"]);
	});
