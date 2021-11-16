<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - EstimateWeeklyTrafficFromMonthJob.php
 */

namespace Neo\Jobs\Traffic;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Models\PropertyTraffic;
use Neo\Models\PropertyTrafficMonthly;

class EstimateWeeklyTrafficFromMonthJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $propertyId, protected int $year, protected int $month) {
    }

    public function handle() {
        // List all the weeks we are working on
        $weeks       = [];
        $datePointer = Carbon::create($this->year, $this->month)->startOfWeek();
        $boundary    = Carbon::create($this->year, $this->month)->addMonth();

        do {
            $weeks[] = $datePointer->clone();
            $datePointer->addWeek();
        } while ($datePointer->lte($boundary));

        $weeks[] = $weeks[array_key_last($weeks)]->clone()->endOfWeek();

        // List the month we need to get data from
        $monthTraffic = collect($weeks)
            ->mapWithKeys(fn($d) => ["$d->year-$d->month" => $d])
            ->map(fn($d) => PropertyTrafficMonthly::query()
                                                  ->where("property_id", "=", $this->propertyId)
                                                  ->where("year", "=", $d->year)
                                                  ->where("month", "=", $d->month - 1)
                                                  ->first());

        array_pop($weeks);
        // Now we calculate the traffic for each weeks
        /**
         * @var Carbon $week
         */
        foreach ($weeks as $week) {
            $trafficCount = 0;
            for ($i = 0; $i < 7; $i++) {
                $day       = $week->clone()->addDays($i);
                $monthData = $monthTraffic->get("$day->year-$day->month");

                // We are missing month data for this week, go to the next one
                if (!$monthData) {
                    continue 2;
                }

                $trafficCount += $monthData->final_traffic / $day->daysInMonth;
            }

            PropertyTraffic::query()->updateOrInsert([
                "property_id" => $this->propertyId,
                "year"        => $week->year,
                "week"        => $week->week,
            ], [
                "traffic"     => $trafficCount,
                "is_estimate" => true
            ]);
        }
    }
}
