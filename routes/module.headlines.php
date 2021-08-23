<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.headlines.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\HeadlinesController;
use Neo\Models\Headline;
use Neo\Models\HeadlineMessage;

Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {
    /*
        |----------------------------------------------------------------------
        | Headlines
        |----------------------------------------------------------------------
        */

    Route::model("headline", Headline::class);
    Route::model("headlineMessage", HeadlineMessage::class);

    Route::   get("headlines"           , HeadlinesController::class . "@index");
    Route::   get("headlines/_current"  , HeadlinesController::class . "@current");
    Route::   get("headlines/{headline}", HeadlinesController::class . "@show");
    Route::  post("headlines"           , HeadlinesController::class . "@store");
    Route::   put("headlines/{headline}", HeadlinesController::class . "@update");
    Route::delete("headlines/{headline}", HeadlinesController::class . "@destroy");

    Route::put("headlines/{headline}/messages/{headlineMessage}", HeadlinesController::class . "@updateMessage")
         ;
});
