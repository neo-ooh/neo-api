<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Kernel.php
 */

namespace Neo\Http;

use Fruitcake\Cors\HandleCors;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Neo\Http\Middleware\Authenticate;
use Neo\Http\Middleware\PreventRequestsDuringMaintenance;
use Neo\Http\Middleware\TrimStrings;
use Neo\Http\Middleware\TrustProxies;

class Kernel extends HttpKernel {
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        "default" => [
            "throttle:api",
            SubstituteBindings::class,
            "auth:neo-loa-4",
        ],
        'api'   => [
            'throttle:api',
            SubstituteBindings::class,
        ],
        'loa-4' => [
            'auth:neo-loa-4', // login + unlocked + 2fa + tos
        ],
        'loa-3' => [
            'auth:neo-loa-3', // login + unlocked + 2fa
        ],
        'loa-2' => [
            'auth:neo-loa-2', // login + unlocked
        ],
        'loa-1' => [
            'auth:neo-loa-1', // login
        ],
        'broadsign'   => [
            SubstituteBindings::class,
        ],
        'access-tokens'   => [
            'throttle:api',
            'auth:access-tokens',
            SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth'             => Authenticate::class,
        'auth.basic'       => AuthenticateWithBasicAuth::class,
        'bindings'         => SubstituteBindings::class,
        'cache.headers'    => SetCacheHeaders::class,
        'can'              => Authorize::class,
        'password.confirm' => RequirePassword::class,
        'signed'           => ValidateSignature::class,
        'throttle'         => ThrottleRequests::class,
        'verified'         => EnsureEmailIsVerified::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule): void {
        //
    }
}
