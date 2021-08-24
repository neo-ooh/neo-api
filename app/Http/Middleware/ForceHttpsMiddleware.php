<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ForceHttpsMiddleware.php
 */

namespace Neo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ForceHttpsMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure                  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        URL::forceScheme('https');

        return $next($request);
    }
}
