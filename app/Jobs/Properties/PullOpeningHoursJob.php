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

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\OpeningHours;
use Neo\Models\Property;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\Models\DayPart;
use Neo\Services\Broadcast\PiSignage\PiSignageConfig;

class PullOpeningHoursJob implements ShouldQueue, ShouldBeUnique, ShouldBeUniqueUntilProcessing {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

//    public $delay = 60;

    public function __construct(protected int $propertyId) {
    }

    public function unique() {
        return $this->propertyId;
    }

    public function handle() {
        /** @var Property|null $property */
        $property = Property::query()
                            ->with(["actor:id,name"])
                            ->find($this->propertyId);

        if (!$property) {
            return false;
        }

        $location = $property->actor->own_locations()->first();
        if (!$location) {
            return false;
        }

        $config = Broadcast::network($location->network_id)->getConfig();

        if ($config instanceof PiSignageConfig) {
            // PiSignage is unsupported at this time
            return false;
        }

        $dayPart = DayPart::getByDisplayUnit(new BroadsignClient($config), $location->external_id)
                          ->firstWhere("minute_mask", "!==", "");

        if (!$dayPart) {
            return false;
        }

        $days = collect(explode(";", $dayPart->minute_mask));
        $days = $days->map(fn($day, $i) => array_map(function ($time) use ($i) {
            $tmp     = $time - ($i * 60 * 24);
            $hours   = floor($tmp / 60);
            $minutes = $tmp % 60;

            return str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT);
        }, explode("-", $day)));

        $days->each(function ($day, $i) use ($property) {
            OpeningHours::query()->updateOrInsert([
                "property_id" => $property->getKey(),
                "weekday"     => $i + 1
            ], [
                "open_at"  => $day[0],
                "close_at" => $day[1]
            ]);
        });

        return true;
    }
}
