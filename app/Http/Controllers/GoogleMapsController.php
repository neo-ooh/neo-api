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

use GooglePlaces;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Neo\Http\Requests\GoogleMaps\SearchPlacesRequest;

class GoogleMapsController {
    public function _searchPlaces(SearchPlacesRequest $request) {
        return new Response(GooglePlaces::textSearch($request->input("query"), [
            "language" => App::currentLocale(),
            "location" => $request->input("location"),
            "radius" => 10000,
            "region" => "ca"
        ]));
    }
}
