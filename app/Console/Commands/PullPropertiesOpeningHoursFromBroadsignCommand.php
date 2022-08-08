<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullPropertiesOpeningHoursFromBroadsignCommand.php
 */

namespace Neo\Console\Commands;

use Illuminate\Console\Command;
use Neo\Models\OpeningHours;
use Neo\Models\Property;
use Neo\Modules\Broadcast\Services\BroadSign\API\BroadSignClient;
use Neo\Modules\Broadcast\Services\BroadSign\Models\DayPart;
use Neo\Modules\Broadcast\Services\PiSignage\PiSignageConfig;
use Neo\Services\Broadcast\Broadcast;

class PullPropertiesOpeningHoursFromBroadsignCommand extends Command {
    protected $signature = 'one-off:pull-properties-opening-hours';

    protected $description = 'Pull all the properties opening hours from BroadSign';

    public function handle() {
        $properties = Property::with("actor:id,name")->lazy(100);

        /** @var Property $property */
        foreach ($properties as $property) {
            $location = $property->actor->own_locations()->first();
            if (!$location) {
                $this->warn($property->actor->name . ": No locations");
                continue;
            }

            $config = Broadcast::network($location->network_id)->getConfig();

            if ($config instanceof PiSignageConfig) {
                // PiSignage is unsupported at this time
                $this->warn($property->actor->name . ": PiSignage");
                continue;
            }

            $dayPart = DayPart::getByDisplayUnit(new BroadSignClient($config), $location->external_id)
                              ->firstWhere("minute_mask", "!==", "");

            if (!$dayPart) {
                $this->warn($property->actor->name . ": No day part");
                continue;
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

            $this->info($property->actor->name . ": OK");
        }
    }
}
