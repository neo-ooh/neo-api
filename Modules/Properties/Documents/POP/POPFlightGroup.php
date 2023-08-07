<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPFlightGroup.php
 */

namespace Neo\Modules\Properties\Documents\POP;

use Spatie\LaravelData\Data;

class POPFlightGroup extends Data {
	public function __construct(
		public string|null $name,

		public array       $provinces,
		public array       $markets,
		public array       $cities,
		public array       $tags,

		public array       $lines = [],
	) {
	}
}
