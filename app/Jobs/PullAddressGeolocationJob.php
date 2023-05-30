<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullAddressGeolocationJob.php
 */

namespace Neo\Jobs;

use Exception;
use Geocoder\Laravel\ProviderAndDumperAggregator as Geocoder;
use Geocoder\Model\Coordinates;
use Geocoder\Provider\Geonames\Model\GeonamesAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Models\Address;

class PullAddressGeolocationJob implements ShouldQueue, ShouldBeUnique {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Address $address) {
    }

    public function uniqueId() {
        return $this->address->getKey();
    }

    public function handle(Geocoder $geocoder) {
        $this->findGeolocation($geocoder);

        $this->findTimezone($geocoder);

        $this->address->save();
    }

    /**
     * @param Geocoder $geocoder
     * @return void
     */
    protected function findGeolocation(Geocoder $geocoder): void {
        try {
            $res = $geocoder->geocode($this->address->string_representation)->get();
            clock($res);
        } catch (Exception $e) {
            clock("Could not geocode address:" . $this->address->string_representation);
            clock($e);
            return;
        }

        if ($res->isEmpty()) {
            // No geolocation could be found for address
            return;
        }

        // We got results, take the first one and save it.
        /** @var \Geocoder\Model\Address $result */
        $result = $res->first();

        /** @var Coordinates $response */
        $coordinates                = $result->getCoordinates();
        $this->address->zipcode     = str_replace(" ", "", $result->getPostalCode());
        $this->address->geolocation = new Point($coordinates->getLatitude(), $coordinates->getLongitude());
    }

    protected function findTimezone(Geocoder $geocoder) {
        if (!$this->address->geolocation) {
            return;
        }

        try {
            // Now fetch the timezone of the address
            [$lng, $lat] = [$this->address->geolocation->longitude, $this->address->geolocation->latitude];

            $responses = $geocoder->using("geonames")
                                  ->reverse($lat, $lng)
                                  ->get();
        } catch (Exception $e) {
            clock("Could not get timezone for address:" . $this->address->string_representation);
            clock($e);
            return;
        }

        if ($responses->isEmpty()) {
            return;
        }

        /** @var GeonamesAddress $geoNameResponse */
        $geoNameResponse         = $responses->first();
        $this->address->timezone = $geoNameResponse->getTimezone();

        $this->address->save();
    }
}
