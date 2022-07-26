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
use Carbon\CarbonInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Neo\Models\PropertyTraffic;
use Neo\Models\PropertyTrafficMonthly;
use Symfony\Component\Console\Output\ConsoleOutput;

class EstimateWeeklyTrafficFromMonthJob implements ShouldQueue {
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $propertyId, protected int $year, protected int $month) {
    }

    public function handle() {
        $output = new ConsoleOutput();

        // List all the weeks we are working on
        $weeks = [];
        // Special case for january
        $datePointer = Carbon::create($this->year, $this->month)->startOfWeek(CarbonInterface::MONDAY);
        $boundary    = Carbon::create($this->year, $this->month)->addMonth();

        do {
            $weeks[] = $datePointer->clone();
            $datePointer->addWeek();
        } while ($datePointer->lte($boundary));

        $weeks[] = $weeks[array_key_last($weeks)]->clone()->endOfWeek(CarbonInterface::SUNDAY);

        // List the month we need to get data from
        $monthTraffic = collect($weeks)
            ->mapWithKeys(fn($d) => ["$d->year-$d->month" => $d])
            ->map(fn($d) => PropertyTrafficMonthly::query()
                                                  ->where("property_id", "=", $this->propertyId)
                                                  ->where("year", "=", $d->year)
                                                  ->where("month", "=", $d->month - 1)
                                                  ->first());

        array_pop($weeks);

        $weeklyTraffic = [];

        // Now we calculate the traffic for each weeks
        /**
         * @var Carbon $week
         */
        foreach ($weeks as $week) {
            $weekNumber = strftime("%W", $week->timestamp) + 1;
            $output->writeln("[Week #$weekNumber] {$week->toFormattedDateString()} -> {$week->clone()->endOfWeek()->toFormattedDateString()}");

            $trafficCount     = 0;
            $foundDaysTraffic = 0;

            for ($i = 0; $i < 7; ++$i) {
                $day       = $week->clone()->addDays($i);
                $monthData = $monthTraffic->get("$day->year-$day->month");

                // We are missing month data for this week, use the default month instead
                if (!$monthData && ($day->month !== $this->month || $day->year !== $this->year)) {
                    $monthData = $monthTraffic->get("$this->year-$this->month");
                }

                if (!$monthData) {
<<<<<<< HEAD
                    $output->writeln("[$this->year-$this->month-$day->day] => " . "No data!");
=======
                    $output->writeln("[$this->year-$this->month-$day->day] => " . ($monthData ? $monthData->final_traffic : "No data!"));
>>>>>>> 318f02d3 (traffic: Prevent creation of weekly traffic entry if some days of traffic are missing)
                    continue;
                }

                $trafficCount += $monthData->final_traffic / $day->daysInMonth;
                $foundDaysTraffic++;
            }

            $output->writeln("[Week #$weekNumber] $foundDaysTraffic days of traffic found");

            if ($foundDaysTraffic < 7) {
                $output->writeln("[Week #$weekNumber] Incomplete week of data, leave empty");
                // Not enough information for this week, remove any records
                PropertyTraffic::query()->where("property_id", "=", $this->propertyId)
                               ->where("year", "=", $week->weekYear)
                               ->where("week", "=", $weekNumber)
                               ->delete();
                continue;
            }

            $weeklyTraffic[$weekNumber] = $trafficCount;

            PropertyTraffic::query()->updateOrInsert([
                "property_id" => $this->propertyId,
                "year"        => $week->weekYear,
                "week"        => $weekNumber,
            ], [
                "traffic"     => $trafficCount,
                "is_estimate" => true
            ]);
        }

        if ($this->month === 12 && Carbon::create($this->year, $this->month)
                                         ->endOfMonth()
                                         ->startOfWeek(CarbonInterface::MONDAY)
                                         ->subDay()->isoWeek === 52) {


            $output->writeln("Force adding 53rd week");

            PropertyTraffic::query()->updateOrInsert([
                "property_id" => $this->propertyId,
                "year"        => $this->year,
                "week"        => 53,
            ], [
                "traffic"     => $weeklyTraffic[52],
                "is_estimate" => true
            ]);
        }

        // All good, Push the new values to Odoo
        PushPropertyTrafficJob::dispatch($this->propertyId);

        // And clear our cache
        Cache::forget("property-$this->propertyId-rolling-weekly-traffic");
    }
}
