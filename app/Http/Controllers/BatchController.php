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
use Illuminate\Support\Facades\Route;
use Neo\Http\Requests\BatchRequest;

class BatchController extends Controller {
    public function handle(BatchRequest $request) {
        $requests  = $request->input("requests");
        $responses = [];

        foreach ($requests as $request) {
            $internalRequests = Request::create($request["uri"], $request["method"], $request["payload"] ?? []);
            $response         = Route::dispatch($internalRequests);
            $responses[]      = [
                "id"       => $request["id"],
                "status"   => $response->getStatusCode(),
                "headers"  => $response->headers->all(),
                "response" => json_decode($response->getContent()),
            ];
        }

        return new Response($responses);
    }
}
