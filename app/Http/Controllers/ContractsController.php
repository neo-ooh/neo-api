<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractsController.php
 */

namespace Neo\Http\Controllers;

use Edujugon\Laradoo\Exceptions\OdooException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Exceptions\Odoo\ContractAlreadyExistException;
use Neo\Exceptions\Odoo\ContractIsCancelledException;
use Neo\Exceptions\Odoo\ContractIsDraftException;
use Neo\Exceptions\Odoo\ContractNotFoundException;
use Neo\Http\Requests\Contracts\DestroyContractRequest;
use Neo\Http\Requests\Contracts\ListContractsRequest;
use Neo\Http\Requests\Contracts\RefreshContractRequest;
use Neo\Http\Requests\Contracts\ShowContractRequest;
use Neo\Http\Requests\Contracts\StoreContractRequest;
use Neo\Http\Requests\Contracts\UpdateContractRequest;
use Neo\Jobs\Contracts\ImportContractDataJob;
use Neo\Jobs\Contracts\ImportContractJob;
use Neo\Jobs\Contracts\ImportContractReservations;
use Neo\Jobs\Contracts\RefreshContractsPerformancesJob;
use Neo\Models\Contract;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    /**
     * @throws OdooException
     */
    public function store(StoreContractRequest $request): Response {
        // The contract ID is a sensitive value, as it is the one that will be used to match additional campaigns with the contract.
        // Now, for some f***ing reason, hyphens are not made equal, and contract name are not standardized. So we want to make sure all stored contract name uses hyphen-minus, which is the default hyphen on a keyboard (looking at you Word).
        $contractId = strtoupper(str_replace(mb_chr(8208, 'UTF-8'), '-', $request->input("contract_id")));

        //First, we check if the contract is already present in the db, we don't want any duplicates
        if (Contract::query()->where("contract_id", "=", $contractId)->exists()) {
            throw new ContractAlreadyExistException();
        }

        // We want to pull the contract from Odoo and check its status. Only contracts that are either sent or done are accepted. Otherwise, the contract is still a proposal and we don't want that.
        $odooClient   = OdooConfig::fromConfig()->getClient();
        $odooContract = \Neo\Services\Odoo\Models\Contract::findByName($odooClient, $contractId);

        if (!$odooContract) {
            throw new ContractNotFoundException($contractId);
        }

        if ($odooContract->isDraft()) {
            throw new ContractIsDraftException($contractId);
        }

        if ($odooContract->isCancelled()) {
            throw new ContractIsCancelledException($contractId);
        }

        ImportContractJob::dispatchSync($contractId, $odooContract);

        $contract = Contract::query()->where("contract_id", "=", $odooContract->name)->first();

        if (!$contract) {
            throw new HttpException(400, "Could not import contract.");
        }

        return new Response($contract, 201);
    }

    public function show(ShowContractRequest $request, Contract $contract): Response {
        return new Response($contract->loadPublicRelations());
    }

    public function update(UpdateContractRequest $request, Contract $contract): Response {
        $contract->salesperson_id = $request->input("salesperson_id");
        $contract->save();

        return new Response($contract->loadPublicRelations());
    }

    public function refresh(RefreshContractRequest $request, Contract $contract): Response {
        set_time_limit(120);

        if ($request->input("reimport", false)) {
            ImportContractDataJob::dispatch($contract->id);
        }

        ImportContractReservations::dispatch($contract->id)->chain([
                                                                       new RefreshContractsPerformancesJob($contract->id),
                                                                   ]);

        return new Response(["status" => "ok"]);
    }

    public function destroy(DestroyContractRequest $request, Contract $contract): void {
        $contract->delete();
    }
}
