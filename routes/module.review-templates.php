<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.review-templates.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Modules\Broadcast\Http\Controllers\ReviewsTemplatesController;
use Neo\Modules\Broadcast\Models\ReviewTemplate;

Route::group([
    "middleware" => "default",
    "prefix"     => "v1"
], function () {

    Route::model("template", ReviewTemplate::class);

    Route::   get("review-templates", ReviewsTemplatesController::class . "@index");
    Route::  post("review-templates", ReviewsTemplatesController::class . "@store");
    Route::   put("review-templates/{template}", ReviewsTemplatesController::class . "@update");
    Route::delete("review-templates/{template}", ReviewsTemplatesController::class . "@destroy");
});
