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
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Location;
use Neo\Models\Property;
use Neo\Models\Province;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Symfony\Component\Console\Output\ConsoleOutput;

class PullPropertyAddressFromBroadSignJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle() {
        // For each property, we will see if it's associated group has at leas one location.
        // If so, we pull the adress and lat/lng from its matching display unit in BroadSign and fill in our addresses
        $properties = Property::query()->with("actor.own_locations")->get();

        /** @var Property $property */
        foreach ($properties as $property) {

            // No locations, do nothing
            if($property->actor->own_locations->count() === 0) {
                (new ConsoleOutput())->writeln("no locations:" . $property->actor->name);
                continue;
            }

            /** @var Location $location */
            $location = $property->actor->own_locations->first();
            if($location->province === '--') {
                (new ConsoleOutput())->writeln("no province:" . $property->actor->name);
                continue; // ignore
            }

            /** @var Province $province */
            $province = Province::query()->where("slug", "=", $location->province)->first();
            /** @var City $city */
            $city = City::query()->firstOrCreate([
                "province_id" => $province->id,
                "name" => $location->city
            ]);

            $address = $property->address ?? new Address();
            $address->city_id = $city->id;

            $networkconfig = Broadcast::network($location->network_id)->getConfig();
            $displayUnit = \Neo\Services\Broadcast\BroadSign\Models\Location::get(new BroadsignClient($networkconfig), $location->external_id);

            // Extract additional information from the address
            // Matches:
            // [0] => Full address
            // [1] => Street #
            // [2] => Street Name
            // [3] => City
            // [4] => Province
            // [5] => Zip code
            if (preg_match('/(^\d*)\s([.\-\w\s]+),\s*([.\-\w\s]+),\s*([A-Z]{2})\s(\w\d\w\s*\d\w\d)/iu', $displayUnit->address, $matches)) {
                $address->line_1 = trim($matches[1]) . " " .trim($matches[2]) ;
                $address->zipcode = str_replace(" ", "", trim($matches[5]));
            }

            [$lat, $lng] = explode(",", substr($displayUnit->geolocation, 1, -1));
            $address->geolocation = new Point($lat, $lng);

            $address->save();
            $property->address_id = $address->id;
            $property->save();
        }
    }
}
