<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPFlightNetwork.php
 */

namespace Neo\Modules\Properties\Documents\POP;

use Spatie\LaravelData\Data;

class POPFlightNetwork extends Data {
	public function __construct(
		public int   $network_id,

		public float $contracted_impressions,
		public float $contracted_media_value,
		public float $contracted_net_investment,

		public float $delivered_impressions,
		public float $delivered_impressions_factor,
	) {
	}
}
