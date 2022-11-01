<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullOpeningHoursJob.php
 */

namespace Neo\Jobs\Properties;

use ArrayIterator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\OpeningHours;
use Neo\Models\Property;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Exceptions\UnsupportedBroadcasterFunctionalityException;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterLocations;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class PullOpeningHoursJob implements ShouldQueue, ShouldBeUnique, ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//    public $delay = 60;

    public function __construct(protected int $propertyId) {
    }

    public function unique(): int {
        return $this->propertyId;
    }

    /**
     * @throws UnsupportedBroadcasterFunctionalityException
     * @throws UnknownProperties
     * @throws InvalidBroadcasterAdapterException
     */
    public function handle(): bool {
        /** @var Property|null $property */
        $property = Property::query()
                            ->with(["actor:id,name"])
                            ->find($this->propertyId);

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

        $openingHours = $broadcaster->getLocationOpeningHours($location->toExternalBroadcastIdResource());

        foreach ($openingHours->days as $i => $times) {
            OpeningHours::query()->updateOrInsert([
                "property_id" => $property->getKey(),
                "weekday"     => $i + 1,
            ], [
                "open_at"  => $times[0],
                "close_at" => $times[1],
            ]);
        }

        return true;
    }
}
