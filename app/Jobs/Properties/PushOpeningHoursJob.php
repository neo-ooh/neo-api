<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PushOpeningHoursJob.php
 */

namespace Neo\Jobs\Properties;

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
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterLocations;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\Resources\OpeningHours as OpeningHoursResource;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

class PushOpeningHoursJob implements ShouldQueue, ShouldBeUnique, ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $delay;

    public function __construct(protected int $propertyId) {
        $this->delay = 120;
    }

    public function unique() {
        return $this->propertyId;
    }

    /**
     * @throws UnknownProperties
     * @throws InvalidBroadcasterAdapterException
     */
    public function handle(): void {
        /** @var Property|null $property */
        $property = Property::query()
                            ->with(["actor:id,name", "actor.own_locations:id,network_id,external_id", "opening_hours"])
                            ->find($this->propertyId);

        if (!$property) {
            return;
        }

        // Map the property's opening hours to the intermediary format
        // While doing so, advance opening hours by 15 minutes and delay closing hours by 15 minutes
        // This delay leaves time for the screen to boot up, etc.
        $openingHours = new OpeningHoursResource(days: $property->opening_hours->map(function (OpeningHours $hours) {
            $openAt  = $hours->open_at->clone()->subMinutes(15)->max($hours->open_at->clone()->startOfDay());
            $closeAt = $hours->close_at->clone()->addMinutes(15)->min($hours->close_at->clone()->endOfDay());
            return [$openAt->format("H:i"), $closeAt->format("H:i")];
        }));

        /** @var Location $location */
        foreach ($property->actor->own_locations as $location) {
            /** @var BroadcasterOperator & BroadcasterLocations $broadaster */
            $broadcaster = BroadcasterAdapterFactory::makeForNetwork($location->network_id);

            if (!$broadcaster->hasCapability(BroadcasterCapability::Locations)) {
                continue;
            }

            $broadcaster->setLocationOpeningHours($location->toExternalBroadcastIdResource(), $openingHours);
        }
    }
}
