<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPRequest.php
 */

namespace Neo\Modules\Properties\Documents\POP;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class POPRequest extends Data {
	public function __construct(
		public int            $contract_id,
		public string         $contract_number,

		public string         $salesperson,
		public string         $client,
		public string|null    $presented_to,
		public string         $advertiser,

		public string         $locale,

		/**
		 * @var 'flights'|'buy-types'
		 */
		public string         $summary_breakdown,

		#[DataCollectionOf(POPFlight::class)]
		public DataCollection $flights,
	) {
	}
}
