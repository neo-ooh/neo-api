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
use Symfony\Component\Console\Output\ConsoleOutput;

class EstimateWeeklyTrafficFromMonthJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected int $propertyId, protected int $year, protected int $month) {
    }

    public function handle() {
        $output = new ConsoleOutput();

        // List all the weeks we are working on
        $weeks = [];
        // Special case for january
        $datePointer = Carbon::create($this->year, $this->month)->startOfWeek(Carbon::MONDAY);
        $boundary    = Carbon::create($this->year, $this->month)->addMonth();

        do {
            $weeks[] = $datePointer->clone();
            $datePointer->addWeek();
        } while ($datePointer->lte($boundary));

        $weeks[] = $weeks[array_key_last($weeks)]->clone()->endOfWeek(Carbon::SUNDAY);

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
            $output->writeln("Week #$week->week...");

            $trafficCount = 0;
            for ($i = 0; $i < 7; $i++) {
                $day       = $week->clone()->addDays($i);
                $monthData = $monthTraffic->get("$day->year-$day->month");

                $output->writeln("[$day->year-$day->month-$day->day] => " . ($monthData ? $monthData->final_traffic : "Not found!"));

                // We are missing month data for this week, use the default month instead
                if (!$monthData && ($day->month !== $this->month || $day->year !== $this->year)) {
                    $monthData = $monthTraffic->get("$this->year-$this->month");

                    $output->writeln("[$this->year-$this->month-$day->day] => " . ($monthData ? $monthData->final_traffic : "Not found!"));
                }

                if (!$monthData) {
                    continue;
                }

                $trafficCount += $monthData->final_traffic / $day->daysInMonth;
            }

            $weeklyTraffic[$week->week] = $trafficCount;

            PropertyTraffic::query()->updateOrInsert([
                "property_id" => $this->propertyId,
                "year"        => $week->weekYear,
                "week"        => $week->week,
            ], [
                "traffic"     => $trafficCount,
                "is_estimate" => true
            ]);
        }
        if ($this->month === 12 && Carbon::create($this->year, $this->month)
                                         ->endOfMonth()
                                         ->startOfWeek(Carbon::MONDAY)
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
    }

}
