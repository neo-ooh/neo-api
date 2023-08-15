<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignScheduleLocation.php
 */

namespace Neo\Modules\Broadcast\Models\Structs;

use Spatie\LaravelData\Data;

class CampaignScheduleLocation extends Data {
	public function __construct(
		public int $campaign_id,
		public int $schedule_id,
		public int $location_id,
	) {
	}
}
