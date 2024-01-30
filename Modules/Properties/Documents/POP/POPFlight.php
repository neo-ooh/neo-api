<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPFlight.php
 */

namespace Neo\Modules\Properties\Documents\POP;

use Neo\Modules\Properties\Models\ContractLine;
use Neo\Resources\FlightType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class POPFlight extends Data {
	public function __construct(
		public bool           $include,
		public int            $flight_id,
		public string         $flight_name,
		public FlightType     $flight_type,
		public string         $start_date,
		public string         $end_date,

		#[DataCollectionOf(POPFlightNetwork::class)]
		public DataCollection $networks,

		/**
		 * @var 'properties'|'categories'|'products'
		 */
		public string|null    $breakdown,

		#[DataCollectionOf(POPFlightGroup::class)]
		public DataCollection $groups,

		#[DataCollectionOf(POPScreenshot::class)]
		public DataCollection $screenshots,

		/**
		 * @var ContractLine[]
		 */
		public array          $lines = []
	) {
	}

    /**
     * This function updates the `performances` of the lines of networks with a `delivered_impressions_factor` that is not 1.0
     * @return void
     */
    public function applyDeliveryFactorToLines() {
        /** @var POPFlightNetwork $network */
        foreach ($this->networks as $network) {
            if(1.0 - $network->delivered_impressions_factor < PHP_FLOAT_EPSILON) {
                continue; // No adjustment
            }

            $adjustedDeliveredImpressions = $network->delivered_impressions * $network->delivered_impressions_factor;

            $networkLines = collect($this->lines)
                ->where("product.network_id", "===", $network->network_id)
                ->sortBy(fn(ContractLine $line) => $line->performances->impressions, descending: true);

            if($networkLines->isEmpty()) {
                continue;
            }

            $lines = collect([...$networkLines]);
            $linesToAdjust = [$lines->shift()];
            $linesToAdjustCount = 1;
            $currentFloor = $linesToAdjust[0]->performances->impressions;
            $deliveredImpressions = $network->delivered_impressions;

            /*
             * The process here is as follow:
             * We get the difference between the current line impressions and the next one. We look at what it would look like if
             * we were to remove this value from all the lines already parsed.
             * If the resulting total is below our goal, we adjust and stop. If not, we continue
             */
            while ($lines->isNotEmpty()) {
                /** @var ContractLine $next */
                $next = $lines->shift();
                $diff = $currentFloor - $next->performances->impressions;

                $toRemove = $diff * $linesToAdjustCount;

                if(($deliveredImpressions - $toRemove) > $adjustedDeliveredImpressions) {
                    // Not enough, substract and continue
                    $deliveredImpressions -= $diff * $linesToAdjustCount;
                    $currentFloor = $next->performances->impressions;

                    $linesToAdjust[] = $next;
                    $linesToAdjustCount += 1;

                    continue;
                }

                // We reached the adjusted value we want. we have to get as close as possible to it as we can
                $offshoot = $adjustedDeliveredImpressions - ($deliveredImpressions - $toRemove);
                $toRemove -= $offshoot;

                $currentFloor -= $toRemove / $linesToAdjustCount;
                break;
            }

            /** @var ContractLine $line */
            foreach ($linesToAdjust as $line) {
                $factor = $currentFloor / $line->performances->impressions;
                $line->performances->impressions = $currentFloor;
                $line->performances->repetitions *= $factor;
            }
       }
    }
}
