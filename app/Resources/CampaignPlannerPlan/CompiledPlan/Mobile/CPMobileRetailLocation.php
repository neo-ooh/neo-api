<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPMobileRetailLocation.php
 */

namespace Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile;

use Spatie\LaravelData\Data;

class CPMobileRetailLocation extends Data {
	public function __construct(
		public string $name,
		public string $address,
	) {
	}
}
