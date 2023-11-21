<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ImportContractReservations.php
 */

namespace Neo\Modules\Properties\Jobs\Contracts;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Models\BroadcasterConnection;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Neo\Modules\Broadcast\Services\BroadcasterScheduling;
use Neo\Modules\Broadcast\Services\Resources\CampaignSearchResult;
use Neo\Modules\Properties\Models\Contract;
use Neo\Modules\Properties\Models\ContractFlight;
use Neo\Modules\Properties\Models\ContractReservation;
use Neo\Resources\FlightType;

/**
 * This job lists and store a reference to the reservations in BroadSign that matches the contract number.
 *
 * @package Neo\Jobs
 *
 */
class ImportContractReservations implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	protected int $contractId;

	/**
	 * Create a new job instance.
	 */
	public function __construct(int $contractId) {
		$this->contractId = $contractId;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 * @throws InvalidBroadcasterAdapterException
	 */
	public function handle(): void {
		/** @var Contract|null $contract */
		$contract = Contract::query()->find($this->contractId);

		if (!$contract) {
			// Contract does not exist, stop here
			return;
		}

		$contract->load("flights");

		/** @var \Illuminate\Database\Eloquent\Collection<BroadcasterConnection> $broadcastersConnections */
		$broadcastersConnections = BroadcasterConnection::query()->where("contracts", "=", true)->get();

		/** @var Collection<CampaignSearchResult> $externalCampaigns */
		$externalCampaigns = collect();

		/** @var BroadcasterConnection $broadcastersConnection */
		foreach ($broadcastersConnections as $broadcastersConnection) {
			/** @var BroadcasterOperator & BroadcasterScheduling $broadcaster */
			$broadcaster = BroadcasterAdapterFactory::makeForBroadcaster($broadcastersConnection->getKey());

			$identifier        = strtoupper($contract->contract_id);
			$externalCampaigns = $externalCampaigns->merge($broadcaster->findCampaigns($identifier));

			$identifier        = strtoupper(str_replace('-', '_', $contract->contract_id));
			$externalCampaigns = $externalCampaigns->merge($broadcaster->findCampaigns($identifier));
		}

		$externalCampaigns = $externalCampaigns->unique(fn(CampaignSearchResult $searchResult) => "{$searchResult->id->broadcaster_id}-{$searchResult->id->external_id}")
		                                       ->filter(fn(CampaignSearchResult $searchResult) => $searchResult->enabled === true);

		$storedReservationsId = [];

		// Now make sure all reservations are properly associated with the report
		/** @var CampaignSearchResult $externalCampaign */
		foreach ($externalCampaigns as $externalCampaign) {
			// Ignore disabled campaigns
			if (!$externalCampaign->enabled) {
				continue;
			}

			/** @var ContractReservation $cr */
			$cr = ContractReservation::query()->firstOrNew([
				                                               "broadcaster_id" => $externalCampaign->id->broadcaster_id,
				                                               "external_id"    => $externalCampaign->id->external_id,
			                                               ]);

			// Make sure information about the campaign are up to date
			$cr->contract_id   = $contract->id;
			$cr->name          = $externalCampaign->name;
			$cr->original_name = $externalCampaign->name;
			$cr->start_date    = Carbon::parse($externalCampaign->start_date . " " . $externalCampaign->start_time);
			$cr->end_date      = Carbon::parse($externalCampaign->end_date . " " . $externalCampaign->end_time);

			// If the contract has a plan attached, do not associate campaigns by default as they may be part of the contract's campaigns
			if (!$contract->has_plan) {
				if ($contract->flights->count() === 1) {
					// If only one flight, assign by default
					$cr->flight_id = $contract->flights->first()->id;
				} else if (!$cr->flight_id) {
					/** @var ContractFlight|null $flight */
					$flight = $contract->flights()->where("start_date", "=", $externalCampaign->start_date)
					                   ->where("end_date", "=", $externalCampaign->end_date)
					                   ->when(str_ends_with($externalCampaign->name, "BUA"), function ($query) {
						                   $query->where("type", "=", FlightType::BUA);
					                   })->when(!str_ends_with($externalCampaign->name, "BUA"), function ($query) {
							$query->where("type", "!=", FlightType::BUA);
						})
					                   ->first();

					if ($flight) {
						$cr->flight_id = $flight->id;
					}
				}
			}

			$cr->save();

			$storedReservationsId[] = $cr->id;
		}

		$contract->reservations()->whereNotIn("id", $storedReservationsId)->delete();

		Cache::tags($contract->contract_id)->flush();
	}
}
