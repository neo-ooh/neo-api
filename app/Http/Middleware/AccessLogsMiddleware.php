<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AccessLogsMiddleware.php
 */

namespace Neo\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccessLogsMiddleware {
    public function handle(Request $request, Closure $next) {
        $requestData = [
            "path"    => $request->getBasePath(),
            "query"   => $request->query->all(),
            "payload" => $request->json(),
            "client"  => [
                "ip"         => $request->getClientIp(),
                "id"         => $request->user()?->id ?? 0,
                "name"       => $request->user()?->name ?? '-',
                "user-agent" => $request->userAgent(),
            ]
        ];

        Log::channel("activity")->info($requestData);

        return $next($request);
    }
}
