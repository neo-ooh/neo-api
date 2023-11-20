<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportMissingContractsJob.php
 */

namespace Neo\Jobs\Contracts;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\InventoryAdapter;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\InventoryCapability;
use Neo\Modules\Properties\Services\Resources\ContractResource;
use Neo\Modules\Properties\Services\Resources\Enums\ContractState;
use Symfony\Component\Console\Output\ConsoleOutput;

class ImportMissingContractsJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct() {
	}

	public function handle() {
		// Our goal here is to list all contracts in Odoo that have been modified those past 4 days, keep the ones that has a contract sent/contract signed status, and make sure they are correctly imported in Odoo.
		$providers   = InventoryProvider::query()->get();
		$inventories = $providers->map(fn(InventoryProvider $provider) => InventoryAdapterFactory::make($provider))
		                         ->filter(fn(InventoryAdapter $adapter) => $adapter->hasCapability(InventoryCapability::ContractsRead));

		/** @var InventoryAdapter $inventory */
		foreach ($inventories as $inventory) {
			$contracts = $inventory->listContracts(Carbon::now()->subDays(4));

			/** @var ContractResource $contract */
			foreach ($contracts as $contract) {
				// Ignore contracts that are not locked.
				if ($contract->state !== ContractState::Locked) {
					continue;
				}

				// Try to import the contract in Connect.
				(new ConsoleOutput())->writeln("Importing contract $contract->name...");
				ImportContractJob::dispatch($contract->name);
			}
		}
	}
}
