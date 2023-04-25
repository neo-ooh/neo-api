<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullPropertyAddressFromBroadSignJob.php
 */

namespace Neo\Jobs;

use ArrayIterator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Province;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcastResource;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterLocations;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Properties\Models\Property;

class PullPropertyAddressFromBroadSignJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $property_id) {
    }

    /**
     * @return bool
     * @throws InvalidBroadcasterAdapterException
     * @throws InvalidBroadcastResource
     */
    public function handle(): bool {
        // For each property, we will see if it's associated group has at leas one location.
        // If so, we pull the address and lat/lng from its matching external representation
        /** @var Property|null $property */
        $property = Property::query()
                            ->with(["actor:id,name"])
                            ->find($this->property_id);

        if (!$property) {
            return false;
        }

        /** @var ArrayIterator<int, Location> $locationsIterator */
        $locationsIterator = $property->actor->own_locations->getIterator();

        do {
            /** @var Location $location */
            $location = $locationsIterator->current();

            /** @var (BroadcasterOperator&BroadcasterLocations) $broadcaster */
            $broadcaster = BroadcasterAdapterFactory::makeForNetwork($location->network_id);

            if (!$broadcaster->hasCapability(BroadcasterCapability::Locations)) {
                $broadcaster = null;
            }
        } while ($broadcaster === null && $locationsIterator->valid());

        if (!$broadcaster) {
            // Could not found a broadcaster supporting locations, stop here
            return false;
        }

        $externalLocation = $broadcaster->getLocation($location->toExternalBroadcastIdResource());

        // Collect models for components of the address
        /** @var Province $province */
        $province = Province::query()->where("slug", "=", $externalLocation->province)->first();

        /** @var City $city */
        $city = City::query()->firstOrCreate([
                                                 "province_id" => $province->id,
                                                 "name"        => $externalLocation->city,
                                             ]);

        // Fill the address
        $address              = $property->address ?? new Address();
        $address->line_1      = $externalLocation->address;
        $address->city_id     = $city->getKey();
        $address->zipcode     = $externalLocation->zipcode;
        $address->geolocation = new Point($externalLocation->lat, $externalLocation->lng);
        $address->save();

        $property->address_id = $address->id;
        $property->save();

        return true;
    }
}
