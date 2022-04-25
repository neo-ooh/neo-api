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
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Response;
use Neo\Http\Requests\Foursquare\SearchPlacesRequest;
use Neo\Models\Brand;

class FoursquareController {
    public function _searchPlaces(SearchPlacesRequest $request) {
        $client = new Client();

        try {
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
        } catch (ClientException $e) {
            return new Response([]);
        }

        if ($response->getStatusCode() !== 200) {
            return new Response([]);
        }

        $places = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        $places->results = array_filter($places->results, fn($place) => $place->location->country === "CA");

        $formattedPlaces = array_map(fn($place) => [
            "external_id" => $place->fsq_id,
            "name"        => $place->name,
            "address"     => ($place->location->address ?? "") . ', ' . ($place->location->postcode ?? "") . ' ' . ($place->location->locality ?? "") . ', ' . ($place->location->region ?? ""),
            "query"       => $request->input("q"),
            "position"    => [
                "coordinates" => [$place->geocodes->main->longitude, $place->geocodes->main->latitude],
                "type"        => "Point",
            ]
        ], $places->results);

        $brands = [];

        if ($request->input("brands", false)) {
            $brands = Brand::query()
                           ->whereFullText(["name_en", "name_fr"], $request->input("q"))
                           ->orWhere("name_en", "=", $request->input("q"))
                           ->orWhere("name_fr", "=", $request->input("q"))
                           ->has("pointsOfInterest")
                           ->orderBy("name_en")
                           ->orderBy("name_fr")
                           ->withCount("pointsOfInterest")
                           ->get()
                           ->where("points_of_interest_count", ">", 0);
        }

        return new Response([
            "pois"   => array_values($formattedPlaces),
            "brands" => $brands,
        ]);
    }
}
