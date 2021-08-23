<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - core.auth.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ActorsController;
use Neo\Http\Controllers\LoginController;
use Neo\Http\Controllers\PasswordRecoveryController;
use Neo\Http\Controllers\TermsOfServiceController;
use Neo\Http\Controllers\TwoFactorAuthController;
use Neo\Http\Controllers\WelcomeController;

Route::group([
    "middleware" => "guests",
    "prefix" => "v1/auth"
], function () {
    /*
    |----------------------------------------------------------------------
    | First access
    |----------------------------------------------------------------------
    */

    Route:: get('/welcome', WelcomeController::class . '@check'      );
    Route::post('/welcome', WelcomeController::class . '@setPassword');


    /*
    |----------------------------------------------------------------------
    | First Factor Authentication
    |----------------------------------------------------------------------
    */
    Route::post('/login', LoginController::class . '@login');


    /*
    |----------------------------------------------------------------------
    | Token Refresh
    |----------------------------------------------------------------------
    */
    Route::middleware('loa-1')->group(function() {
        Route::get('/token-refresh', ActorsController::class . '@getToken');
    });


    /*
    |----------------------------------------------------------------------
    | Second Factor Authentication
    |----------------------------------------------------------------------
    */
    Route::middleware('loa-2')->group(function() {
        Route::post('/two-fa-validation', TwoFactorAuthController::class . '@validateToken');
        Route::post('/two-fa-refresh', TwoFactorAuthController::class . '@refresh');
    });


    /*
    |----------------------------------------------------------------------
    | Password Recovery
    |----------------------------------------------------------------------
    */
    Route::prefix('recovery')->group(function () {
        // Generate a password recovery token
        Route::post('/recover-password', PasswordRecoveryController::class . '@makeToken'    );
        Route::post('/check-token'     , PasswordRecoveryController::class . '@validateToken');
        Route::post('/reset-password'  , PasswordRecoveryController::class . '@resetPassword');
    });


    /*
    |----------------------------------------------------------------------
    | Terms of service
    |----------------------------------------------------------------------
    */
    Route::middleware('loa-3')->group(function() {
        Route:: get('/terms-of-service', TermsOfServiceController::class . '@show'  );
        Route::post('/terms-of-service', TermsOfServiceController::class . '@accept');
    });
});
