<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FoursquareController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Foursquare\SearchPlacesRequest;

class FoursquareController {
    public function _searchPlaces(SearchPlacesRequest $request) {
        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', 'https://api.foursquare.com/v3/places/search', [
            "query"   => [
                "query" => $request->input("q"),
                "ne"    => $request->input("bounds")[0],
                "sw"    => $request->input("bounds")[1],
            ],
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => config('services.foursquare.key')
            ],
        ]);

        return new Response($response->getBody());
    }
}
