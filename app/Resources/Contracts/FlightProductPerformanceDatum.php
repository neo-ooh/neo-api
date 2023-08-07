<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FlightProductPerformanceDatum.php
 */

namespace Neo\Resources\Contracts;

use Spatie\LaravelData\Data;

class FlightProductPerformanceDatum extends Data {
	public function __construct(
		public int $flight_id,
		public int $product_id,
		public int $repetitions,
		public int $impressions,
	) {
	}
}
