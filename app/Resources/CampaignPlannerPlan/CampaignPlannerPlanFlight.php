<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerPlanFlight.php
 */

namespace Neo\Resources\CampaignPlannerPlan;

use Neo\Resources\Contracts\FlightType;
use Spatie\LaravelData\Data;

class CampaignPlannerPlanFlight extends Data {
	public function __construct(
		public string      $id,
		public string|null $name,
		public FlightType  $type,

		public string      $start_date,
		public string      $end_date,
		public string      $start_time,
		public string      $end_time,
		public int         $number,

		public array       $selection,
		public array       $filters,

		public array       $discounts,
		public array       $groups,
		public array       $insights,

		public bool        $send,

		public string      $updated_at,
	) {

	}
}
