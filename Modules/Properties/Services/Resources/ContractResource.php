<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractResource.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Neo\Modules\Properties\Services\Resources\Enums\ContractState;

class ContractResource extends InventoryResource {
	public function __construct(
		/**
		 * Name of the contract, usually the visible contract number
		 */
		public string                  $name,
		/**
		 * @var InventoryResourceId ID of the contract in the external inventory
		 */
		public InventoryResourceId     $contract_id,

		/**
		 * @var ContractState State of the contract
		 */
		public ContractState           $state,

		/**
		 * @var SalespersonResource Salesperson owning this contract
		 */
		public SalespersonResource     $salesperson,

		/**
		 * @var ClientResource|null Client of the contract
		 */
		public ClientResource|null     $client,

		/**
		 * @var AdvertiserResource|null Advertiser for the contract
		 */
		public AdvertiserResource|null $advertiser,

		/**
		 * @var iterable<ContractLineResource> $lines lines of the contract, may be empty if only the contract information where fetched
		 */
		public iterable                $lines = []
	) {
	}
}
