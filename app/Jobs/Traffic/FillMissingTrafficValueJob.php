<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FillMissingTrafficValueJob.php
 */

namespace Neo\Jobs\Traffic;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Date;
use Neo\Models\Property;
use Neo\Models\PropertyTrafficMonthly;
use Symfony\Component\Console\Output\ConsoleOutput;


/**
 * This job fill in the internal traffic value for properties whose traffic is missing for the current month
 */
class FillMissingTrafficValueJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle() {
        $output = new ConsoleOutput();

        $date         = Date::now()->subMonths(1);
        $currentYear  = $date->year;
        $currentMonth = $date->month - 1;

        $properties = Property::with(["traffic", "traffic.data", "address", "address.city", "address.city.province"])->get();

        $output->writeln("Checking traffic value for " . $date->toDateString());

        /** @var Property $property */
        foreach ($properties as $property) {
            // Check if the property has a record for this month traffic
            if ($property->traffic->data->first(fn(PropertyTrafficMonthly $t) => $t->year === $currentYear && $t->month === $currentMonth)) {
                // ignore
                $output->writeln($property->actor->name . " - has traffic");
                continue;
            }

            // No record, we need to create one.

            // Check the missing value strategy for the property.
            // If it is set to default value, we will use this one,
            if ($property->traffic->missing_value_strategy === "USE_PLACEHOLDER") {
                $output->writeln($property->actor->name . " - using placeholder");

                PropertyTrafficMonthly::query()->create([
                    "property_id" => $property->actor_id,
                    "year"        => $currentYear,
                    "month"       => $currentMonth,
                    "temporary"   => $property->traffic->placeholder_value
                ]);

                EstimateWeeklyTrafficFromMonthJob::dispatch($property->getKey(), $currentYear, $currentMonth);

                continue;
            }

            // Do we have a record for the same month in 2019 ?
            /** @var ?PropertyTrafficMonthly $prevRecord */
            $prevRecord = $property->traffic->data->first(fn(PropertyTrafficMonthly $t) => $t->year === 2019 && $t->month === $currentMonth);

            if (!$prevRecord) {
                $output->writeln($property->actor->name . " - no previous record");
                continue;
            }

            $coef    = $property->address->city->province->slug === 'QC' ? .80 : .70;
            $traffic = $prevRecord->final_traffic * $coef;

            PropertyTrafficMonthly::query()->create([
                "property_id" => $property->actor_id,
                "year"        => $currentYear,
                "month"       => $currentMonth,
                "temporary"   => $traffic
            ]);

            $output->writeln($property->actor->name . " - reused previous record");

            EstimateWeeklyTrafficFromMonthJob::dispatch($property->getKey(), $currentYear, $currentMonth);
        }

    }
}
