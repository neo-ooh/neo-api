<?php

namespace Neo\Http\Controllers;

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
use Neo\Models\Contract;
use Neo\Models\ContractFlight;
use Neo\Services\Odoo\OdooConfig;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ContractsController extends Controller {
    public function index(ListContractsRequest $request) {
        $salespersonId = $request->input("actor_id", null);

        if (!$salespersonId && !Gate::allows(Capability::contracts_manage)) {
            $salespersonId = Auth::id();
        }

        return new Response(Contract::query()
                                    ->when($salespersonId !== null, fn(Builder $query) => $query->where("salesperson_id", "=", $salespersonId))
                                    ->orderBy("contract_id")
                                    ->get()
                                    ->append(["start_date", "end_date", "expected_impressions", "received_impressions"])
                                    ->makeHidden('reservations'));
    }

    public function recent(ListContractsRequest $request) {
        $contracts = Contract::query()->where("salesperson_id", "=", Auth::id())
                             ->whereHas("flights", function (Builder $query) {
                                 $query->where("start_date", "<", Date::now());
                                 $query->where("end_date", ">", Date::now());
                             })
                             ->limit(5)
                             ->get()
                             ->append(["start_date", "end_date", "expected_impressions", "received_impressions"])
                             ->sortBy("end_date", 0, "asc")
                             ->makeHidden('reservations')->values();

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

        if ($odooContract->state === 'cancelled') {
            throw new ContractIsCancelledException($contractId);
        }

        ImportContractJob::dispatchSync($contractId, $odooContract);

        $contract = Contract::query()->where("contract_id", "=", $odooContract->name)->first();

        if (!$contract) {
            throw new HttpException(400, "Could not import contract.");
        }

        return new Response($contract, 201);
    }

    public function show(ShowContractRequest $request, Contract $contract) {
        $with = $request->get("with", []);

        $contract->append(["start_date", "end_date", "expected_impressions"]);

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

            if (in_array("reservations.locations", $with, true)) {
                $contract->loadReservationsLocations();
            }
        }

        if (in_array("flights", $with, true)) {
            $contract->load("flights");
            $contract->flights->append("expected_impressions");

            // Add the network ID to each order line
            $contract->flights->each(fn(ContractFlight $flight) => $flight->lines->append(["network_id", "product_type"]));
        }

        if (in_array("performances", $with, true)) {
            $contract->append("performances");
        }

        if (in_array("bursts", $with, true)) {
            $contract->load("bursts", "bursts.screenshots", "bursts.location");
        }

        if (in_array("validated_screenshots", $with, true)) {
            $contract->load("validated_screenshots");
        }

        return new Response($contract);
    }

    public function update(UpdateContractRequest $request, Contract $contract) {
        $contract->salesperson_id = $request->input("salesperson_id");
        $contract->save();

        return new Response($contract);
    }

    public function refresh(RefreshContractRequest $request, Contract $contract) {
        ImportContractDataJob::dispatchSync($contract->id);
        ImportContractReservations::dispatchSync($contract->id);

        return new Response(["status" => "ok"]);
    }

    public function destroy(DestroyContractRequest $request, Contract $contract) {
        $contract->delete();
    }
}
