<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GoogleMapsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Neo\Http\Requests\GoogleMaps\SearchPlacesRequest;
use SKAgarwal\GoogleApi\Exceptions\GooglePlacesApiException;
use SKAgarwal\GoogleApi\Exceptions\InvalidRequestException;
use SKAgarwal\GoogleApi\Exceptions\NotImplementedException;
use SKAgarwal\GoogleApi\Exceptions\OverQueryLimitException;
use SKAgarwal\GoogleApi\Exceptions\RequestDeniedException;
use SKAgarwal\GoogleApi\Exceptions\UnknownErrorException;

class GoogleMapsController {
    public function _searchPlaces(SearchPlacesRequest $request) {
        try {
            $response = GooglePlaces::textSearch($request->input("query"), [
                "language" => App::currentLocale(),
                "location" => $request->input("location"),
                "radius"   => 10000,
                "region"   => "ca",
            ]);

            return new Response($response);
        } catch (GooglePlacesApiException|InvalidRequestException|OverQueryLimitException|RequestDeniedException|UnknownErrorException|NotImplementedException $exception) {
            Log::error("google.places.api.error", [
                "message" => $exception->getErrorMessage(),
            ]);

            return new Response(["message" => $exception->getErrorMessage()], 400);
        }

    }
}
