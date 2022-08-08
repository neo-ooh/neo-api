<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Handler.php
 */

namespace Neo\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler {
    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register() {
        $this->renderable(function (BaseException $e, Request $request) {
            if ($request->expectsJson()) {
                return new Response([
                    "code"    => $e->getCode(),
                    "message" => $e->getMessage(),
                ], $e->getStatus());
            }

            return new Response(["message" => $e->getMessage(), "code" => $e->getCode()], 500);
        });
    }
}
