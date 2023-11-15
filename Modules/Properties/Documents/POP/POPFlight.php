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
}
