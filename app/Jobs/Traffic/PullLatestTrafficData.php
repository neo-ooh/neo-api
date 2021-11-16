<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PullLatestTrafficData.php
 */

namespace Neo\Jobs\Traffic;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\Property;
use Neo\Models\PropertyTrafficMonthly;
use Neo\Services\Traffic\Traffic;

class PullLatestTrafficData implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        // This job goes through all properties with a defined source and pulls the traffic data for the past month
        $properties = Property::query()->whereHas("traffic", function (Builder $query) {
            $query->where("input_method", "=", "LINKETT");
        })->get();

        if ($properties->count() === 0) {
            // Nothing to o
            return;
        }

        // we can pull the source before looping as ther is only one supported source for now.
        // When more sources get supported, we will need to move this inside the loop
        $source = Traffic::from($properties->first()->traffic->source->first());
        $start  = Carbon::now()->subMonth()->startOfMonth();
        $end    = Carbon::now()->startOfMonth();

        foreach ($properties as $property) {
            $traffic = $source->getTraffic($property, $start, $end);

            // If the traffic value is 0 and there is already a record, we ignore it
            if ($traffic === 0 && PropertyTrafficMonthly::query()->where([
                    "property_id" => $property->actor_id,
                    "year"        => $start->year,
                    "month"       => $start->month - 1
                ])->exists()) {
                continue;
            }

            // Save the value
            PropertyTrafficMonthly::query()->updateOrCreate([
                "property_id" => $property->actor_id,
                "year"        => $start->year,
                "month"       => $start->month - 1,
            ], [
                "traffic" => $traffic,
            ]);

            EstimateWeeklyTrafficFromMonthJob::dispatch($property->getKey(), $start->year, $start->month);
        }

        // We're good
    }
}
