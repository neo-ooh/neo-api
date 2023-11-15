<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - OdooToContractUpdater.php
 */

namespace Neo\Resources\CampaignPlannerPlan\Utils;

use Neo\Resources\CampaignPlannerPlan\CPPlanContract;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

/*
 * class CPPlanContract extends Data {
 *   public function __construct(
 *  	public string     $contract,
 *  	public array      $salespersonName,
 *  	public array      $partnerName,
 *  	public null|array $analyticAccountName,
 *  	public string     $date,
 * ) {}
 * }
 *
 * */

class OdooToContractUpdater implements Cast {
	public function cast(DataProperty $property, mixed $value, array $context): mixed {
		if ($value instanceof CPPlanContract) {
			return $value;
		}

		if (key_exists("salespersonName", $value)) {
			// Passed value is a legacy odoo-specific contract association
			return new CPPlanContract(
				inventory_id    : 1,
				contract_id     : $value["contract"],
				salesperson_id  : $value["salespersonName"][0],
				salesperson_name: $value["salespersonName"][1],
				client_id       : key_exists("partnerName", $value) ? $value["partnerName"][0] : null,
				client_name     : key_exists("partnerName", $value) ? $value["partnerName"][1] : null,
				advertiser_id   : key_exists("analyticAccountName", $value) ? $value["analyticAccountName"][0] : null,
				advertiser_name : key_exists("analyticAccountName", $value) ? $value["analyticAccountName"][1] : null,
				association_date: $value["date"],
			);
		}

		return CPPlanContract::from($value);
	}
}
