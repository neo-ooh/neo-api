<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPPlanRoot.php
 */

namespace Neo\Resources\CampaignPlannerPlan;

use Neo\Resources\CampaignPlannerPlan\Utils\OdooToContractUpdater;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class CPPlanRoot extends Data {
	public function __construct(
		public array               $layers,
		public array               $flights,
		public array               $compiled_selections,
		public array               $compiled_flights,

		#[MapInputName("odoo")]
		#[WithCast(OdooToContractUpdater::class)]
		public CPPlanContract|null $contract,
		public array               $settings,
		public array               $columns,
	) {

	}
}
