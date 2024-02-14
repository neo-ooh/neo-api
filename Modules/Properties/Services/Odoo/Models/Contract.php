<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Contract.php
 */

namespace Neo\Modules\Properties\Services\Odoo\Models;

use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Support\Collection;
use JsonException;
use Neo\Modules\Properties\Services\Odoo\API\OdooClient;
use Neo\Modules\Properties\Services\Resources\AdvertiserResource;
use Neo\Modules\Properties\Services\Resources\ClientResource;
use Neo\Modules\Properties\Services\Resources\ContractResource;
use Neo\Modules\Properties\Services\Resources\Enums\ContractState;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Neo\Modules\Properties\Services\Resources\SalespersonResource;

/**
 * @property int        $id
 * @property string     $name
 * @property string     $display_name
 * @property string     $date_order
 * @property string     $create_date
 * @property array      $user_id
 * @property array      $partner_id
 * @property array      $partner_invoice_id
 * @property array      $analytic_account_id
 * @property array      $order_line
 * @property array      $company_id
 * @property array<int> $campaign_ids
 * @property string     $access_url
 * @property string     $state
 */
class Contract extends OdooModel {
	public static string $slug = "sale.order";

	protected static array $filters = [];

	public static function findByName(OdooClient $client, string $contractName): static|null {
		return static::findBy($client, "name", $contractName)->first();
	}

	public function isDraft(): bool {
		return $this->state === 'draft';
	}

	public function isCancelled(): bool {
		return $this->state === 'cancel';
	}

	/**
	 * Tell if the contract is confirmed, and its content should be taken into account for availabilities, etc.
	 *
	 * @return bool
	 */
	public function isConfirmed(): bool {
		return !$this->isDraft() && !$this->isCancelled();
	}

	/*
	|--------------------------------------------------------------------------
	| Actions
	|--------------------------------------------------------------------------
	*/

	/**
	 * @return void Remove all lines in Contract
	 */
	public function clearLines() {
		$this->client->custom(static::$slug, "action_del_lines", [$this->getKey()]);
	}

	/*
	|--------------------------------------------------------------------------
	| Attachements
	|--------------------------------------------------------------------------
	*/

	/**
	 * Get an attachment of the contract by its given name
	 *
	 * @param string $attachmentName
	 * @return Attachment|null
	 * @throws OdooException
	 * @throws JsonException
	 */
	public function getAttachment(string $attachmentName) {
		return Attachment::all($this->client, [
			["res_model", "=", static::$slug],
			["res_id", "=", $this->getKey()],
			["name", "=", $attachmentName],
		])->first();
	}

	/**
	 * Stores the given `rawData` as an attachment to the contract using the given name
	 *
	 * @param string $attachmentName
	 * @param string $data
	 * @return int
	 * @throws JsonException
	 */
	public function storeAttachment(string $attachmentName, string $data): int {
		return Attachment::create($this->client, [
			"res_model" => static::$slug,
			"res_id"    => $this->getKey(),

			"name" => $attachmentName,

			"type"  => 'binary',
			"datas" => $data,
		], pullRecord:            false);
	}

	/**
	 * Delete attachment for the contract using the given name
	 *
	 * @param string $attachmentName
	 * @return Collection|string|true
	 */
	public function removeAttachment(string $attachmentName) {
		return Attachment::delete($this->client, [
			["res_model", "=", static::$slug],
			["res_id", "=", $this->getKey()],
			["name", "=", $attachmentName],
		]);
	}

	public function toResource(int $inventoryId): ContractResource {
		return new ContractResource(
			name       : $this->name ?? $this->getKey(),
			contract_id: new InventoryResourceId(
				             inventory_id: $inventoryId,
				             external_id : $this->getKey(),
				             type        : InventoryResourceType::Contract,
			             ),
			state      : $this->state === 'draft' ? ContractState::Draft : ContractState::Locked,
			salesperson: new SalespersonResource(
				             inventory_id: $inventoryId,
				             external_id : $this->user_id[0],
				             name        : $this->user_id[1],
			             ),
			client     : $this->partner_id
				             ? new ClientResource(
					             inventory_id: $inventoryId,
					             external_id : $this->partner_id[0],
					             name        : $this->partner_id[1]
				             )
				             : null,
			advertiser : $this->analytic_account_id
				             ? new AdvertiserResource(
					             inventory_id: $inventoryId,
					             external_id : $this->analytic_account_id[0],
					             name        : $this->analytic_account_id[1],
				             )
				             : null
		);
	}
}
