<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherBundleTargeting.php
 */

namespace Neo\Modules\Dynamics\Models\Structs;

use Spatie\LaravelData\Data;

class WeatherBundleTargeting extends Data {
	public function __construct(
		public array $provinces,
		public array $markets,
		public array $cities,
		public array $properties,

		public array $excluded_properties,
	) {
	}
}
