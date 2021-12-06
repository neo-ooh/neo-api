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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AccessLogsMiddleware {
    public function handle(Request $request, Closure $next) {
        $requestData = [
            "env"     => config("app.env"),
            "method"  => $request->getMethod(),
            "path"    => $request->getPathInfo(),
            "query"   => $request->query->all(),
            "payload" => $request->json(),
            "headers" => $request->headers->all(),
            "client"  => [
                "ip"         => $request->getClientIp(),
                "id"         => $request->user()?->id ?? 0,
                "name"       => $request->user()?->name ?? '-',
                "user-agent" => $request->userAgent(),
            ]
        ];

        /** @var Response $response */
        $response = $next($request);

        if ($response instanceof Response) {
            $requestData["response"] = [
                "status" => $response->status(),
            ];
        }

        Log::channel("activity")->info("connect.access", $requestData);

        return $response;
    }
}
