<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalContractResource.php
 */

namespace Neo\Modules\Properties\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Neo\Modules\Properties\Services\Resources\ContractResource;

/** @mixin ContractResource */
class ExternalContractResource extends JsonResource {
	public function toArray(Request $request): array {
		return [
			"name"             => $this->name,
			"inventory_id"     => $this->contract_id->inventory_id,
			"contract_id"      => $this->contract_id->external_id,
			"state"            => $this->state,
			"salesperson_id"   => $this->salesperson->external_id,
			"salesperson_name" => $this->salesperson->name,
			"client_id"        => $this->client?->external_id,
			"client_name"      => $this->client?->name,
			"advertiser_id"    => $this->advertiser?->external_id,
			"advertiser_name"  => $this->advertiser?->name,
		];
	}
}
