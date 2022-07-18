<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullPropertyAddressFromBroadSignJob.php
 */

namespace Neo\Jobs;

use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Property;
use Neo\Models\Province;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Symfony\Component\Console\Output\ConsoleOutput;

class PullPropertyAddressFromBroadSignJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $property_id) {
    }

    public function handle() {
        // For each property, we will see if it's associated group has at leas one location.
        // If so, we pull the adress and lat/lng from its matching display unit in BroadSign and fill in our addresses
        /** @var Property $property */
        $property = Property::query()->with("actor.own_locations")->find($this->property_id);

        // No locations, do nothing
        if ($property->actor->own_locations->count() === 0) {
            (new ConsoleOutput())->writeln("no locations:" . $property->actor->name);
            return;
        }

        /** @var Location $location */
        $location = $property->actor->own_locations->first();
        if ($location->province === '--') {
            (new ConsoleOutput())->writeln("no province:" . $property->actor->name);
            return; // ignore
        }

        /** @var Province $province */
        $province = Province::query()->where("slug", "=", $location->province)->first();
        /** @var City $city */
        $city = City::query()->firstOrCreate([
            "province_id" => $province->id,
            "name"        => $location->city
        ]);

        $address          = $property->address ?? new Address();
        $address->city_id = $city->id;

        $networkConfig = Broadcast::network($location->network_id)->getConfig();
        $displayUnit   = \Neo\Services\Broadcast\BroadSign\Models\Location::get(new BroadsignClient($networkConfig), $location->external_id);

        // Extract additional information from the address
        // Matches:
        // [0] => Full address
        // [1] => Street #
        // [2] => Street Name
        // [3] => City
        // [4] => Province
        // [5] => Zip code
        if (preg_match('/(^\d*)\s([.\-\w\s]+),\s*([.\-\w\s]+),\s*([A-Z]{2})\s(\w\d\w\s*\d\w\d)/iu', $displayUnit->address, $matches)) {
            $address->line_1  = trim($matches[1]) . " " . trim($matches[2]);
            $address->zipcode = str_replace(" ", "", trim($matches[5]));
        }

        [$lng, $lat] = explode(",", substr($displayUnit->geolocation, 1, -1));
        $address->geolocation = new Point($lat, $lng);

        $address->save();
        $property->address_id = $address->id;
        $property->save();
    }
}
