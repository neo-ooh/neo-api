<?php

namespace Neo\Services\Isochrone;

use Carbon\CarbonInterface;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\Polygon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Neo\Modules\Dynamics\Exceptions\CouldNotFetchThirdPartyDataException;
use function Ramsey\Uuid\v4;

/**
 * Travel Time Isochrone API Client
 *
 * @link https://docs.traveltime.com/api/reference/isochrones Reference
 */
class TravelTimeClient implements IsochroneAdapter {
    /**
     * @link https://docs.traveltime.com/api/reference/isochrones#arrival_searches-transportation-type
     */
    public const ALLOWED_TRAVEL_METHODS = [
        "cycling",
        "driving",
        "driving+train", // Only in the UK
        "public_transport",
        "walking",
        "coach",
        "bus",
        "train",
        "ferry",
        "driving+ferry",
        "cycling+ferry",
        "cycling+public_transport", // Only in the netherlands
    ];

    public function __construct(
        protected string $appID,
        protected string $appKey,
    ) {

    }

    public function getIsochrone(
        float  $lng,
        float  $lat,
        int    $durationMin,
        string $travelMethod
    ) {
        $responseBody = Cache::remember("$lng-$lat-$durationMin-$travelMethod", 3600, function () use ($travelMethod, $durationMin, $lat, $lng) {
            $client = new Client();

            try {
                $response = $client->post("https://api.traveltimeapp.com/v4/time-map", [
                    "headers" => [
                        "Accept"           => "application/json",
                        "Content-Type"     => "application/json",
                        "X-Application-Id" => $this->appID,
                        "X-Api-Key"        => $this->appKey,
                    ],
                    "body"    => json_encode(
                          [
                              "arrival_searches" => [
                                  [
                                      "id"             => v4(),
                                      "coords"         => [
                                          "lng" => $lng,
                                          "lat" => $lat,
                                      ],
                                      "arrival_time"   => Carbon::create(2024, 02, 01, 12, 0)
                                                                ->startOfWeek(CarbonInterface::MONDAY),
                                      "travel_time"    => min($durationMin * 60, 14400 /* 4hrs max */), // Minutes to seconds
                                      "transportation" => [
                                          "type" => $travelMethod,
                                      ],
                                      "range"          => [
                                          "enabled" => true,
                                          "width"   => round($durationMin * .1) * 60, // 10% of requested duration + minutes to seconds
                                      ],
                                  ],
                              ],
                          ]
                        , JSON_THROW_ON_ERROR),
                ]);
            } catch (RequestException $e) {
                throw new CouldNotFetchThirdPartyDataException($e->getResponse());
            }

            if ($response->getStatusCode() !== 200) {
                throw new CouldNotFetchThirdPartyDataException($response);
            }

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        });

        // TravelTime uses a custom format for geometries in its response. We want to convert that to GeoJson for compatibility
        return $this->convertTTShapesToFeatureCollection($responseBody["results"][0]["shapes"]);
    }

    protected function convertTTShapesToFeatureCollection(array $ttshapes) {
        return new FeatureCollection(
            array_map(function (array $shape) {
                return new Feature(new Polygon([
                                                   array_map(function (array $coord) {
                                                       return [$coord["lng"], $coord["lat"]];
                                                   }, $shape["shell"]),
                                                   ...array_map(function (array $hole) {
                                                       return array_map(function (array $coord) {
                                                           return [$coord["lng"], $coord["lat"]];
                                                       }, $hole);
                                                   }, $shape["holes"]),
                                               ]));
            }, $ttshapes)
        );
    }
}