<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdUnitExternalId.php
 */

namespace Neo\Modules\Properties\Services\PlaceExchange\Models\Attributes;

use Spatie\LaravelData\Data;

class AdUnitExternalId extends Data {
	/**
	 * @param string $source
	 * @param array  $uids
	 */
	public function __construct(
		public string $source,
		public array  $uids,
	) {
	}
}
