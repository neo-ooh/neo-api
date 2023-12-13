<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Requests\Contracts\DestroyContractRequest;
use Neo\Http\Requests\Contracts\ListContractsRequest;
use Neo\Http\Requests\Contracts\RefreshContractRequest;
use Neo\Http\Requests\Contracts\ShowContractRequest;
use Neo\Http\Requests\Contracts\UpdateContractRequest;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Jobs\Performances\FetchCampaignsPerformancesJob;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Properties\Jobs\Contracts\ImportContractJob;
use Neo\Modules\Properties\Jobs\Contracts\ImportContractReservations;
use Neo\Modules\Properties\Jobs\Contracts\RefreshContractsPerformancesJob;
use Neo\Modules\Properties\Models\Contract;
use Neo\Modules\Properties\Models\InventoryProvider;
use Neo\Modules\Properties\Services\Exceptions\InvalidInventoryAdapterException;
use Neo\Modules\Properties\Services\InventoryAdapterFactory;
use Neo\Modules\Properties\Services\Resources\Enums\InventoryResourceType;
use Neo\Modules\Properties\Services\Resources\InventoryResourceId;

class ContractsController extends Controller {
	public function index(ListContractsRequest $request): Response {
		$salespersonId = $request->input("actor_id", null);

		if (!$salespersonId && !Gate::allows(Capability::contracts_manage->value)) {
			$salespersonId = Auth::id();
		}

		return new Response(Contract::query()
		                            ->when($salespersonId !== null, fn(Builder $query) => $query->where("salesperson_id", "=", $salespersonId))
		                            ->orderBy("contract_id")
		                            ->get()
		                            ->loadPublicRelations());
	}


	public function recent(ListContractsRequest $request): Response {
		$contracts = Contract::query()->where("salesperson_id", "=", Auth::id())
		                     ->whereHas("flights", function (Builder $query) {
			                     $query->where("start_date", "<", Date::now());
			                     $query->where("end_date", ">", Date::now());
		                     })
		                     ->limit(5)
		                     ->get()
		                     ->sortBy("end_date", 0, "asc")->values();

		return new Response($contracts);
	}

	public function show(ShowContractRequest $request, Contract $contract): Response {
		return new Response($contract->loadPublicRelations());
	}

	public function update(UpdateContractRequest $request, Contract $contract): Response {
		$contract->group_id       = $request->input("group_id");
		$contract->salesperson_id = $request->input("salesperson_id");
		$contract->is_closed      = $request->input("is_closed");
		$contract->save();

		return new Response($contract->loadPublicRelations());
	}

	/**
	 * @throws InvalidInventoryAdapterException
	 * @throws InvalidBroadcasterAdapterException
	 */
	public function refresh(RefreshContractRequest $request, Contract $contract): Response {
		set_time_limit(120);
		// Clear the contract's cache
		Cache::tags(["contract-performances", $contract->contract_id])->flush();

		// Prepare refresh jobs for the flights' campaigns
		$refreshCampaignsJobs = $contract->flights
			->load("campaigns")
			->pluck("campaigns")
			->flatten()
			->map(fn(Campaign $campaign) => new FetchCampaignsPerformancesJob(campaignId: $campaign->getKey()));

		if ($request->input("reimport", false)) {
			$inventory        = InventoryAdapterFactory::make(InventoryProvider::query()->find($contract->inventory_id));
			$contractResource = $inventory->getContract(new InventoryResourceId(
				                                            inventory_id: $contract->inventory_id,
				                                            external_id : $contract->external_id,
				                                            type        : InventoryResourceType::Contract));

//			if (config("app.env") !== "production") {
			(new ImportContractJob($contract->contract_id, $contractResource))->handle();
			(new ImportContractReservations($contract->id))->handle();
			(new RefreshContractsPerformancesJob($contract->id))->handle();
//			} else {
//				ImportContractJob::dispatch($contract->contract_id, $contractResource)
//				                 ->chain([
//					                         new ImportContractReservations($contract->id),
//					                         new RefreshContractsPerformancesJob($contract->id),
//					                         ...$refreshCampaignsJobs,
//				                         ]);
//			}
		} else {
			ImportContractReservations::dispatch($contract->id)
			                          ->chain([
				                                  new RefreshContractsPerformancesJob($contract->id),
				                                  ...$refreshCampaignsJobs,
			                                  ]);
		}

		return new Response(["status" => "ok"]);
	}

	public function destroy(DestroyContractRequest $request, Contract $contract): void {
		$contract->delete();
	}
}
