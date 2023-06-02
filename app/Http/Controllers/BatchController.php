<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BatchController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Neo\Http\Requests\BatchRequest;
use Symfony\Component\HttpFoundation\ParameterBag;

class BatchController extends Controller {
    public function handle(BatchRequest $request) {
        set_time_limit(0);
        
        $requests  = $request->input("requests");
        $responses = [];

        foreach ($requests as $request) {
            $internalRequest = Request::create($request["uri"], $request["method"], $request["payload"] ?? []);
            $internalRequest->setJson(new ParameterBag($request["payload"] ?? []));
            $internalRequest->headers->set("Accept", "application/json");

            $response    = app()->handle($internalRequest);
            $responses[] = [
                "id"       => $request["id"],
                "status"   => $response->getStatusCode(),
                "headers"  => $response->headers->all(),
                "response" => json_decode($response->getContent()),
            ];
        }

        return new Response($responses);
    }
}
