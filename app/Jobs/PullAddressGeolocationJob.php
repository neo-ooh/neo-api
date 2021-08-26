<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullAddressGeolocationJob.php
 */

namespace Neo\Jobs;

use Geocoder\Laravel\ProviderAndDumperAggregator as Geocoder;
use Geocoder\Model\Coordinates;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Neo\Models\Address;

class PullAddressGeolocationJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Address $address) {
    }

    public function handle(Geocoder $geocoder) {
        $res = $geocoder->geocode($this->address->string_representation)->get();

        if($res->isEmpty()) {
            // No geolocation found for address. clean up and end.
            $this->address->geolocation = null;
            $this->address->save();
            return;
        }

        // We got results, take the first one and save it.
        /** @var Coordinates $response */
        $coordinates = $res->first()->getCoordinates();
        $this->address->geolocation = new Point($coordinates->getLatitude(), $coordinates->getLongitude());
        $this->address->save();
    }
}
