<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPCompiledPlan.php
 */

namespace Neo\Resources\CampaignPlannerPlan\CompiledPlan;

use Illuminate\Support\Collection;
use Neo\Resources\CampaignPlannerPlan\CPPlanContract;
use Neo\Resources\CampaignPlannerPlan\Utils\CPCompiledFlightsTransformer;
use Neo\Resources\CampaignPlannerPlan\Utils\CPCompiledFlightsUpdater;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;

class CPCompiledPlan extends Data {

	public function __construct(
		public string              $version,

		#[WithTransformer(CPCompiledFlightsTransformer::class)]
		#[WithCast(CPCompiledFlightsUpdater::class)]
		public Collection          $flights,
		public array               $columns,

		public CPPlanContract|null $contract,

		public string|null         $save_uid,
		public int|null            $owner_id,
		public string              $compiled_at,
	) {
	}
}
