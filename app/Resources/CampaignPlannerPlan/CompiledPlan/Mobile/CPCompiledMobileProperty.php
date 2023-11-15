<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledMobileProperty.php
 */

namespace Neo\Resources\CampaignPlannerPlan\CompiledPlan\Mobile;

use Spatie\LaravelData\Data;

class CPCompiledMobileProperty extends Data {
	public function __construct(
		public int   $id,

		public array $filters,

		public float $impressions,
		public float $media_value,
		public float $price,
		public float $cpm,
	) {
	}
}
