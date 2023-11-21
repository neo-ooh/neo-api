<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RefreshContracts.php
 */

namespace Neo\Modules\Properties\Jobs\Contracts;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Neo\Modules\Properties\Models\Contract;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\Exceptions\InventoryResourceNotFound;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Resources\Enums\ContractState;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class CreateSignupToken
 *
 * @package Neo\Jobs
 *
 */
class RefreshContracts extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'contracts:update';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update all contracts reservations and performances';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 * @throws InvalidInventoryAdapterException
	 */
	public function handle(): int {
		// Import contract that may be missing from Connect.
		ImportMissingContractsJob::dispatch();

		$contracts = Contract::query()
		                     ->where("start_date", ">", Carbon::now()->subWeek())
		                     ->orwhere("end_date", ">", Carbon::now()->subWeek())
		                     ->get();

		(new ConsoleOutput())->writeln("Parsing {$contracts->count()} contracts...");

		/** @var Contract $contract */
		foreach ($contracts as $contract) {
			// Before doing anything, we want to check the status of the contract.
			// Only contracted contracts are imported in Connect, but a contract from a third-party may change to another status, such as cancelled at anytime. In this case, it is important to purge the contract from Connect. Otherwise, this may break availabilities.
			$inventory = InventoryAdapterFactory::make(InventoryProvider::query()->find($contract->inventory_id));
			try {
				$externalContract = $inventory->getContractInformation(new InventoryResourceId(
					                                                       inventory_id: $contract->inventory_id,
					                                                       external_id : $contract->external_id,
					                                                       type        : InventoryResourceType::Contract,
				                                                       ));
			} catch (InventoryResourceNotFound $e) {
				(new ConsoleOutput())->writeln("Could not find contract $contract->contract_id");
				$contract->delete();
				continue;
			}

			// If the contract could no be found, or is not in a confirmed state, we remove it from Connect.
			if ($externalContract->state !== ContractState::Locked) {
				(new ConsoleOutput())->writeln("Removed contract $contract->contract_id");
				$contract->delete();
				continue;
			}

			ImportContractDataJob::dispatch($contract->id);
			ImportContractReservations::dispatch($contract->id);
			RefreshContractsPerformancesJob::dispatch($contract->id);
		}

		return 0;
	}
}
