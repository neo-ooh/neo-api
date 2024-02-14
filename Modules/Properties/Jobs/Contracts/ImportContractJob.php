<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportContractJob.php
 */

namespace Neo\Modules\Properties\Jobs\Contracts;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Neo\Models\Actor;
use Neo\Modules\Properties\Models\Advertiser;
use Neo\Modules\Properties\Models\Client;
use Neo\Modules\Properties\Models\Contract;
use Neo\Modules\Properties\Services\Resources\ContractResource;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * This job reads information about a contract and then imports it in Connect. Clients and Advertiser are associated as needed.
 */
class ImportContractJob implements ShouldQueue, ShouldBeUnique {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected int|null $contract_id = null;

	public function uniqueId(): string {
		return $this->contract_name;
	}

	public function __construct(protected string $contract_name, protected ContractResource $contract) {
	}

	public function getImportedContractId() {
		return $this->contract_id;
	}

	public function handle() {
		$salesperson = Actor::query()
		                    ->where("is_group", "=", false)
		                    ->where("name", "=", $this->contract->salesperson->name)
		                    ->first();

		if (!$salesperson) {
			(new ConsoleOutput())->writeln($this->contract_name . ": No user found with name {$this->contract->salesperson->name}");

			$currentUser = Auth::user();
			if (!$currentUser) {
				return;
			}

			$salesperson = $currentUser;
		}

		if ($this->contract->advertiser) {
			$advertiser = Advertiser::query()->firstOrCreate([
				                                                 "odoo_id" => $this->contract->advertiser->external_id,
			                                                 ], [
				                                                 "name" => $this->contract->advertiser->name,
			                                                 ]);
		} else {
			$advertiser = null;
		}

		if ($this->contract->client) {
			$client = Client::query()->firstOrCreate([
				                                         "odoo_id" => $this->contract->client->external_id,
			                                         ], [
				                                         "name" => $this->contract->client->name,
			                                         ]);
		} else {
			$client = null;
		}

		$contract = Contract::query()->where("contract_id", "=", $this->contract_name)->first();

		if (!$contract) {
			$contract              = new Contract();
			$contract->contract_id = $this->contract_name;
		}

		$contract->inventory_id   = $this->contract->contract_id->inventory_id;
		$contract->external_id    = $this->contract->contract_id->external_id;
		$contract->salesperson_id = $salesperson->getKey();
		$contract->advertiser_id  = $advertiser?->getKey();
		$contract->client_id      = $client?->getKey();
		$contract->save();

		$this->contract_id = $contract->getKey();

		ImportContractDataJob::dispatchSync($contract->getKey());
		ImportContractReservations::dispatchSync($contract->getKey());
	}
}
