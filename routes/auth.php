<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - auth.php
 */

use Illuminate\Support\Facades\Route;
use Neo\Http\Controllers\ActorsController;
use Neo\Http\Controllers\LoginController;
use Neo\Http\Controllers\PasswordRecoveryController;
use Neo\Http\Controllers\TermsOfServiceController;
use Neo\Http\Controllers\TwoFactorAuthController;
use Neo\Http\Controllers\WelcomeController;

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

Route::prefix("v1/auth")->group(function () {
    /*
    |----------------------------------------------------------------------
    | First access
    |----------------------------------------------------------------------
    */

    Route:: get('/welcome', WelcomeController::class . '@check'      )->name('auth.signin-token.check');
    Route::post('/welcome', WelcomeController::class . '@setPassword')->name('auth.signin-token.set');


    /*
    |----------------------------------------------------------------------
    | First Factor Authentication
    |----------------------------------------------------------------------
    */
    Route::post('/login', LoginController::class . '@login')->name('auth.login');


    /*
    |----------------------------------------------------------------------
    | Token Refresh
    |----------------------------------------------------------------------
    */
    Route::middleware('loa-1')->group(function() {
        Route::get('/token-refresh', ActorsController::class . '@getToken')->name('user-token-refresh');
    });


    /*
    |----------------------------------------------------------------------
    | Second Factor Authentication
    |----------------------------------------------------------------------
    */
    Route::middleware('loa-2')->group(function() {
        Route::post('/two-fa-validation', TwoFactorAuthController::class . '@validateToken')->name('two-fa-validation');
        Route::post('/two-fa-refresh', TwoFactorAuthController::class . '@refresh')->name('two-fa-refresh');
    });


    /*
    |----------------------------------------------------------------------
    | Password Recovery
    |----------------------------------------------------------------------
    */
    Route::prefix('recovery')->group(function () {
        // Generate a password recovery token
        Route::post('/recover-password', PasswordRecoveryController::class . '@makeToken'    )->name("auth.recovery.make-token");
        Route::post('/check-token'     , PasswordRecoveryController::class . '@validateToken')->name("auth.recovery.validate");
        Route::post('/reset-password'  , PasswordRecoveryController::class . '@resetPassword')->name("auth.recovery.reset");
    });


    /*
    |----------------------------------------------------------------------
    | Terms of service
    |----------------------------------------------------------------------
    */
    Route::middleware('loa-3')->group(function() {
        Route:: get('/terms-of-service', TermsOfServiceController::class . '@show'  )->name("auth.tos.show");
        Route::post('/terms-of-service', TermsOfServiceController::class . '@accept')->name("auth.tos.accept");
    });
});
