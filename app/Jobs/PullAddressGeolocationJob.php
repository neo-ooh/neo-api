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
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Address;

class PullAddressGeolocationJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Address $address) {
    }

    public function handle(Geocoder $geocoder) {
        clock($this->address->string_representation);

        try {
            $res = $geocoder->geocode($this->address->string_representation)->get();
            clock($res);
        } catch (Exception $e) {
            clock("Could not geocode address:" . $this->address->string_representation);
            clock($e);
            return;
        }

        if ($res->isEmpty()) {
            // No geolocation found for address. clean up and end.
            $this->address->geolocation = null;
            $this->address->timezone    = "";
            $this->address->save();
            return;
        }

        // We got results, take the first one and save it.
        /** @var \Geocoder\Model\Address $result */
        $result = $res->first();
        /** @var Coordinates $response */
        $coordinates                = $result->getCoordinates();
        $this->address->zipcode     = str_replace(" ", "", $result->getPostalCode());
        $this->address->geolocation = new Point($coordinates->getLatitude(), $coordinates->getLongitude());
        $this->address->save();

        try {
            // Now fetch the timezone of the address
            $geoNameResponse = $geocoder->using("geonames")
                                        ->reverse($coordinates->getLatitude(), $coordinates->getLongitude())
                                        ->get();
        } catch (Exception $e) {
            clock("Could not get timezone for address:" . $this->address->string_representation);
            clock($e);
            return;
        }

        if ($geoNameResponse->isEmpty()) {
            $this->address->timezone = "";
            $this->address->save();
            return;
        }

        /** @var GeonamesAddress $timezone */
        $timezone                = $geoNameResponse->first();
        $this->address->timezone = $timezone->getTimezone();
        $this->address->save();
    }
}
