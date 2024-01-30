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
     * This function updates the `performances` of the lines of networks to match the defined cap
     * @return void
     */
    public function applyDeliveryRatioToLines() {
        /** @var POPFlightNetwork $network */
        foreach ($this->networks as $network) {
            $networkLines = collect($this->lines)
                ->where("product.network_id", "===", $network->network_id);

            $cap = $network->delivered_impressions_factor;
            $deliveryTotal = 0;

            /** @var ContractLine $line */
            foreach ($networkLines as $line) {
                // Ignore line if it has no performances
                if(!$line->performances) {
                    continue;
                }

                $contractedImpressions = $line->impressions;
                $deliveredImpressions = $line->performances->impressions;

                $deliveryFactor = $deliveredImpressions / $contractedImpressions;
                if($deliveryFactor > $cap) {
                    $line->performances->impressions = $contractedImpressions * $cap;
                    $line->performances->repetitions *= $line->performances->impressions / $deliveredImpressions;
                }

                $deliveryTotal += $line->performances->impressions;
            }

            $network->delivered_impressions = $deliveryTotal;
       }
    }
}
