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

use GuzzleHttp\Client;
use Illuminate\Http\Response;
use Neo\Http\Requests\Foursquare\SearchPlacesRequest;

class FoursquareController {
    public function _searchPlaces(SearchPlacesRequest $request) {
        $client = new Client();

        $response = $client->request('GET', 'https://api.foursquare.com/v3/places/search', [
            "query"   => [
                "query" => trim($request->input("q")),
                "ne"    => $request->input("bounds")[1],
                "sw"    => $request->input("bounds")[0],
                "limit" => $request->input("limit", 10),
            ],
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => config('services.foursquare.key')
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            return new Response([]);
        }

        $places = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        $formattedPlaces = array_map(fn($place) => [
            "geometry"   => [
                "coordinates" => [$place->geocodes->main->longitude, $place->geocodes->main->latitude],
                "type"        => "Point",
            ],
            "place_name" => $place->name . ', ' . ($place->location->address ?? "") . ', ' . ($place->location->postcode ?? "") . ' ' . ($place->location->locality ?? "") . ', ' . ($place->location->region ?? ""),
            "id"         => $place->fsq_id,
        ], $places->results);

        return new Response($formattedPlaces);
    }
}
