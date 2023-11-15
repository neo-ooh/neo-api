<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CPPlanContract.php
 */

namespace Neo\Resources\CampaignPlannerPlan;

use Spatie\LaravelData\Data;

class CPPlanContract extends Data {
	public function __construct(
		public int         $inventory_id,
		public string      $contract_id,
		public string      $salesperson_id,
		public string      $salesperson_name,
		public string|null $client_id,
		public string|null $client_name,
		public string|null $advertiser_id,
		public string|null $advertiser_name,

		public string      $association_date,
	) {
	}
}
