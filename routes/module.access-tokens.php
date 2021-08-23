<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - module.access-tokens.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\AccessTokensController;
use Neo\Models\AccessToken;

Route::group([
    "middleware" => "default",
    "prefix" => "v1"
], function () {
    /*
    |----------------------------------------------------------------------
    | Access Token
    |----------------------------------------------------------------------
    */

    Route::model("accessToken", AccessToken::class);

    Route::   get("access-tokens"              , AccessTokensController::class . "@index");
    Route::  post("access-tokens"              , AccessTokensController::class . "@store");
    Route::   get("access-tokens/{accessToken}", AccessTokensController::class . "@show");
    Route::   put("access-tokens/{accessToken}", AccessTokensController::class . "@update");
    Route::delete("access-tokens/{accessToken}", AccessTokensController::class . "@destroy");
});
