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
use Neo\Modules\Broadcast\Models\Location;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;
use Neo\Services\Broadcast\BroadSign\Models\DayPart;

class PushOpeningHoursJob implements ShouldQueue, ShouldBeUnique, ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $delay;

    public function __construct(protected int $propertyId) {
        $this->delay = 120;
    }

    public function unique() {
        return $this->propertyId;
    }

    public function handle() {
        /** @var Property|null $property */
        $property = Property::query()
                            ->with(["actor:id,name", "actor.own_locations:id,network_id,external_id", "opening_hours"])
                            ->find($this->propertyId);

        if (!$property) {
            return;
        }

        // Build the minutes mask from the opening hours as expected by BroadSign
        $mask = $property->opening_hours->map(/**
         * @param OpeningHours $openingHours
         * @param int          $index
         */ function ($openingHours, $i) {
            $startMask = (24 * 60 * $i) + $openingHours->open_at->hour * 60 + $openingHours->open_at->minute;
            $endMask   = (24 * 60 * $i) + $openingHours->close_at->hour * 60 + $openingHours->close_at->minute;

            return $startMask . "-" . $endMask;
        })->join(";");

        /** @var Location $location */
        foreach ($property->actor->own_locations as $location) {
            $networkConfig = Broadcast::network($location->network_id)->getConfig();

            if (!($networkConfig instanceof BroadSignConfig)) {
                // ignore locations that are not on a BroadSign network
                continue;
            }

            $client = new BroadsignClient($networkConfig);

            // We get all the dayparts of the display unit
            $dayParts = DayPart::getByDisplayUnit($client, $location->external_id);

            /** @var DayPart $dayPart */
            foreach ($dayParts as $dayPart) {
                $dayPart->minute_mask = $mask;
                $dayPart->save();
            }
        }
    }
}
