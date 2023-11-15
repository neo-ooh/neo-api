<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledGroup.php
 */

namespace Neo\Resources\CampaignPlannerPlan\CompiledPlan;

use Spatie\LaravelData\Data;

class CPCompiledGroup extends Data {
	public function __construct(
		/**
		 * @var array Ids of all the properties in the group
		 */
		public array       $properties,

		public string|null $name = null,
		public string|null $color = null,
	) {
	}
}
