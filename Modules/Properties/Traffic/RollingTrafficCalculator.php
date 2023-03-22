<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RollingTrafficCalculator.php
 */

namespace Neo\Modules\Properties\Traffic;

use ArrayIterator;
use Neo\Modules\Properties\Enums\TrafficFormat;
use Neo\Modules\Properties\Models\PropertyTrafficSettings;
use Neo\Modules\Properties\Models\WeeklyTrafficDatum;

class RollingTrafficCalculator {
    public function __construct(
        protected PropertyTrafficSettings $traffic,
    ) {
    }

    /**
     * Computes the rolling traffic for the provided traffic
     *
     * @return int[] 53 value arrays
     */
    public function compute(): array {
        return match ($this->traffic->format) {
            TrafficFormat::MonthlyMedian   => $this->computeMonthlyMedian(),
            TrafficFormat::MonthlyAdjusted => $this->computeMonthlyAdjusted(),
            TrafficFormat::DailyConstant   => $this->computeDailyConstant(),
        };
    }

    protected function computeMonthlyMedian(): array {
        $rollingTraffic = [];
        $trafficData    = $this->traffic->weekly_data;

        /** Iterator across all years of data. Each year is an array whose indexes map the weeks  */
        /** @var ArrayIterator<WeeklyTrafficDatum> $yearTrafficIt */
        $yearTrafficIt = $trafficData->groupBy("year")->getIterator();

        if ($yearTrafficIt->count() === 0) {
            return array_fill(1, 53, 0);
        }

        /** List all entries whose value is above zero */
        $validData = $trafficData->where("traffic", "!==", 0);

        /** Median weekly traffic of the property */
        $propertyMedian = $validData->count() > 0
            ? $validData->where("traffic", "!==", 0)->pluck("traffic")->sum() / $validData->count()
            : 0;


        // Loop over each week of a year
        // For each week, We try to do a median of all the entries for this week across all available years of information
        for ($week = 2; $week <= 52; $week++) {
            $yearTrafficIt->rewind();
            $weekTraffic    = 0;
            $weekComponents = 0;

            do {
                $t = $yearTrafficIt->current()->firstWhere("week", "===", $week)?->traffic ?? 0;

                if ($t !== 0) {
                    $weekTraffic += $t;
                    $weekComponents++;
                }

                $yearTrafficIt->next();
            } while ($yearTrafficIt->valid());


            // Do we have at least one entry for this week? If yes, do the median.
            if ($weekComponents > 0) {
                // Append the median to the rolling weekly traffic array
                $rollingTraffic[$week] = round($weekTraffic / $weekComponents);
                continue;
            }

            // We don't have any information for this week, fallback to the placeholder value OR the property median depending on settings
            if ($this->traffic->missing_value_strategy === 'USE_PLACEHOLDER') {
                $weekTraffic = $this->traffic->placeholder_value / 4;
            } else {
                $weekTraffic = $propertyMedian;
            }

            // Append the fallback value to the rolling weekly traffic array
            $rollingTraffic[$week] = round($weekTraffic);
        }

        $firstWeekMedian    = round(($rollingTraffic[2] + $rollingTraffic[52]) / 2);
        $lastWeekMedian     = round(($rollingTraffic[2] + $rollingTraffic[52]) / 2);
        $rollingTraffic[1]  = $firstWeekMedian;
        $rollingTraffic[53] = $lastWeekMedian;

        return $rollingTraffic;
    }

    protected function computeMonthlyAdjusted(): array {
        /** @var int[] $rollingTraffic */
        $rollingTraffic = [];
        // We select the most recent entry with a positive traffic and who is not the 53rd week because this one is tricky
        $mostRecentDatum = $this->traffic->weekly_data->last(fn($datum) => $datum->traffic > 0 && $datum->week !== 53);

        if (!$mostRecentDatum) {
            // Return an empty array if no values at all
            for ($week = 1; $week <= 53; $week++) {
                $rollingTraffic[$week] = 0;
            }
            return $rollingTraffic;
        }

        /** @var WeeklyTrafficDatum $referenceDatum */
        $referenceDatum = $this->traffic->weekly_data->first(
            fn($datum) => $datum->year === $this->traffic->start_year && $datum->week === $mostRecentDatum->week
        );

        if (!$referenceDatum || $referenceDatum->traffic === 0) {
            for ($week = 1; $week <= 53; $week++) {
                $rollingTraffic[$week] = 0;
            }
            return $rollingTraffic;
        }

        $evolution = $mostRecentDatum->traffic / $referenceDatum->traffic;

        for ($week = 2; $week <= 52; $week++) {

            /** @var WeeklyTrafficDatum|null $mostRecentDatumForPeriod */
            $mostRecentDatumForPeriod = $this->traffic->weekly_data->where("week", "=", $week)
                                                                   ->sortBy("year", SORT_REGULAR, "desc")
                                                                   ->first();

            /** @var WeeklyTrafficDatum|null $referenceDatumForPeriod */
            $referenceDatumForPeriod = $this->traffic->weekly_data->first(fn($datum) => $datum->year === $this->traffic->start_year && $datum->week === $week);

            // If the two data are the same, directly apply the factored-down result
            if ($mostRecentDatumForPeriod && $referenceDatumForPeriod &&
                $mostRecentDatumForPeriod->year === $referenceDatumForPeriod->year &&
                $mostRecentDatumForPeriod->week === $referenceDatumForPeriod->week) {
                $rollingTraffic[$week] = round($referenceDatumForPeriod->traffic * $evolution);
                continue;
            }

            $rollingTraffic[$week] = round(max(($referenceDatumForPeriod->traffic ?? 0) * $evolution, ($mostRecentDatumForPeriod->traffic ?? 0)));
        }

        $firstWeekMedian    = round(($rollingTraffic[2] * 2 + $rollingTraffic[52]) / 3);
        $lastWeekMedian     = round(($rollingTraffic[2] + $rollingTraffic[52] * 2) / 3);
        $rollingTraffic[1]  = $firstWeekMedian;
        $rollingTraffic[53] = $lastWeekMedian;

        return $rollingTraffic;
    }

    protected function computeDailyConstant(): array {
        $weeklyTraffic = $this->traffic->placeholder_value * 7;

        $rollingTraffic = [];

        for ($i = 1; $i <= 53; $i++) {
            $rollingTraffic[$i] = $weeklyTraffic;
        }

        return $rollingTraffic;
    }
}
