<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPContractUpdater.php
 */

namespace Neo\Resources\CampaignPlannerPlan\Utils;

use Carbon\Carbon;
use Neo\Resources\CampaignPlannerPlan\CPPlanContract;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class CPContractUpdater implements Cast {
	public function cast(DataProperty $property, mixed $value, array $context): mixed {
		if (is_string($value)) {
			return new CPPlanContract(
				inventory_id    : 1,
				contract_id     : $value,
				salesperson_id  : 0,
				salesperson_name: "",
				client_id       : null,
				client_name     : null,
				advertiser_id   : null,
				advertiser_name : null,
				association_date: Carbon::now()->toISOString(),
			);
		}

		return CPPlanContract::from($value);
	}
}
