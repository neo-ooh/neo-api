<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignLocation.php
 */

namespace Neo\Modules\Broadcast\Models\Structs;

use Neo\Modules\Broadcast\Models\Location;
use Spatie\LaravelData\Data;

class CampaignLocation extends Data {
	public function __construct(
		public int      $location_id,
		public int      $format_id,
		public int      $network_id,

		public Location $location
	) {
	}
}
