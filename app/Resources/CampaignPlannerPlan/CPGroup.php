<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPGroup.php
 */

namespace Neo\Resources\CampaignPlannerPlan;

use Spatie\LaravelData\Data;

class CPGroup extends Data {
	public function __construct(
		public string|null $name,
		public string|null $color,
		public array       $networks,
		public array       $provinces,
		public array       $markets,
		public array       $cities,
		public array       $categories,
		public array       $tags,
		public array       $pricelists,
	) {
	}
}
