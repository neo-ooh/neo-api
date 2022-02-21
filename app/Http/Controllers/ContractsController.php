<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Exceptions\Odoo\CannotAccessAnotherSalespersonContractException;
use Neo\Exceptions\Odoo\ContractAlreadyExistException;
use Neo\Exceptions\Odoo\ContractIsDraftException;
use Neo\Exceptions\Odoo\ContractNotFoundException;
use Neo\Http\Requests\Contracts\DestroyContractRequest;
use Neo\Http\Requests\Contracts\ListContractsRequest;
use Neo\Http\Requests\Contracts\RefreshContractRequest;
use Neo\Http\Requests\Contracts\ShowContractRequest;
use Neo\Http\Requests\Contracts\StoreContractRequest;
use Neo\Jobs\Contracts\ImportContractJob;
use Neo\Jobs\RefreshContractReservations;
use Neo\Models\Actor;
use Neo\Models\Contract;
use Neo\Services\Odoo\OdooConfig;

class ContractsController extends Controller {
    public function index(ListContractsRequest $request) {
        $userToSearch = $request->input("actor_id", Auth::id());

        return new Response(Contract::query()
                                    ->where("salesperson_id", "=", $userToSearch)
                                    ->orderBy("contract_id")
                                    ->get()
                                    ->append(["start_date", "end_date", "expected_impressions", "received_impressions"])
                                    ->makeHidden('reservations'));
    }

    public function recent(ListContractsRequest $request) {
        $contracts = Contract::query()->where("salesperson_id", "=", Auth::id())
                             ->join("contracts_flights", "contracts.id", "=", "contracts_flights.contract_id")
                             ->where("contracts_flights.start_date", "<", Date::now())
                             ->where("contracts_flights.end_date", ">", Date::now())
                             ->orderBy("contracts_flights.end_date", "asc")
                             ->limit(5)
                             ->get()
                             ->append(["start_date", "end_date"]);

        return new Response($contracts);
    }

    public function store(StoreContractRequest $request) {
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

        if ($odooContract->state === 'draft') {
            throw new ContractIsDraftException($contractId);
        }

        $owner = Actor::query()->where("name", "=", $odooContract->user_id[1])->first();

        // If no owner could be matched, we attach it to the current user
        if (!$owner) {
            $owner = Auth::user();
        }

        // Is the user is not allowed to access all contract, we check that it indeed owns the contract
        if ($owner->isNot(Auth::user()) && !Gate::allows(Capability::contracts_manage)) {
            throw new CannotAccessAnotherSalespersonContractException();
        }

        $contract = new Contract([
            "contract_id"    => $contractId,
            "salesperson_id" => $owner->id,
        ]);
        $contract->save();

        ImportContractJob::dispatchSync($contract->getKey(), $odooContract);
        RefreshContractReservations::dispatch($contract->id);

        return new Response($contract, 201);
    }

    public function show(ShowContractRequest $request, Contract $contract) {
        $with = $request->get("with", []);

        $contract->append(["start_date", "end_date"]);

        if (in_array("salesperson", $with, true)) {
            $contract->load("salesperson", "salesperson.logo");
        }

        if (in_array("client", $with, true)) {
            $contract->load("client");
        }

        if (in_array("advertiser", $with, true)) {
            $contract->load("advertiser");
        }

        if (in_array("reservations", $with, true)) {
            $contract->load("reservations");
        }

        if (in_array("flights", $with, true)) {
            $contract->load("flights");
            $contract->flights->append("expected_impressions");
        }

        if (in_array("reservations.locations", $with, true)) {
            $contract->loadReservationsLocations();
        }

        if (in_array("performances", $with, true)) {
            $contract->append("performances");
        }

        if (in_array("bursts", $with, true)) {
            $contract->load("bursts", "bursts.screenshots", "bursts.location");
        }

        return new Response($contract);
    }

    public function refresh(RefreshContractRequest $request, Contract $contract) {
        ImportContractJob::dispatchSync($contract->id);
        RefreshContractReservations::dispatchSync($contract->id);

        return new Response(["status" => "ok"]);
    }

    public function destroy(DestroyContractRequest $request, Contract $contract) {
        $contract->delete();
    }
}
