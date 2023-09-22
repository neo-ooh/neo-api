<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LineDefinition.php
 */

namespace Neo\Jobs\Contracts;

class LineDefinition {
	public function __construct(
		public int    $productId,
		public int    $lineId,
		public float  $spots,
		public float  $media_value,
		public float  $discount,
		public string $discount_type,
		public float  $price,
		public int    $traffic,
		public int    $impressions,
	) {
	}
}
