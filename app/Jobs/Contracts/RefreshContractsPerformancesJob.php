<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RefreshContractsPerformancesJob.php
 */

namespace Neo\Jobs\Contracts;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Resources\Contracts\FlightType;

class RefreshContractsPerformancesJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public function __construct(protected int|null $contract_id = null) {
	}

	public function handle() {
		Cache::tags("contract-performances")->clear();

		// We refresh contract performances for contracts who still have flights running
		$contracts = Contract::query()
		                     ->when($this->contract_id !== null, function (Builder $query) {
			                     $query->where("id", "=", $this->contract_id);
		                     })
		                     ->when($this->contract_id === null, function (Builder $query) {
			                     $query->whereRelation("flights", function (Builder $query) {
				                     $query->where("end_date", ">", Carbon::now()->subDays(2));
			                     });
		                     })
		                     ->with(["flights", "reservations"])
		                     ->get();

		/** @var Contract $contract */
		foreach ($contracts as $contract) {
			/** @var Collection<ContractFlight> $guaranteedFlights */
			$guaranteedFlights = $contract->flights->where("type", "<>", FlightType::BUA);
			$guaranteedFlights->append("performances");

			$contract->received_impressions = $guaranteedFlights->flatMap(fn(ContractFlight $flight) => $flight->performances)
			                                                    ->sum("impressions");

			$contract->save();

		}
	}
}
