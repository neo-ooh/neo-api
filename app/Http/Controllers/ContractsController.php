<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Neo\Http\Requests\Contracts\DestroyContractRequest;
use Neo\Http\Requests\Contracts\ListContractsRequest;
use Neo\Http\Requests\Contracts\RefreshContractRequest;
use Neo\Http\Requests\Contracts\ShowContractRequest;
use Neo\Http\Requests\Contracts\StoreContractRequest;
use Neo\Jobs\RefreshContractReservations;
use Neo\Models\Client;
use Neo\Models\Contract;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\Models\Customer;

class ContractsController extends Controller {
    public function recent(ListContractsRequest $request) {
        $contracts = Contract::query()->where("owner_id", "=", Auth::id())
                             ->orderBy("updated_at", "desc")
                             ->limit(5)
                             ->get();

        return new Response($contracts);
    }

    public function store(StoreContractRequest $request) {
        $clientId = $request->input("client_id");

        // The contract ID is a sensitive value, as it is the one that will be used to match additional campaigns with the contract.
        // Now, for some f***ing reason, hyphens are not made equal, and contract name are not standardized. So we want to make sure all stored contract name uses hyphen-minus, which is the default hyphen on a keyboard (looking at you Word).
        $contractId = strtoupper(str_replace(mb_chr(8208, 'UTF-8'), '-', $request->input("contract_id")));
        $client     = Client::query()->where("broadsign_customer_id", "=", $clientId)->first();

        if (!$client) {
            $customer = Customer::get(new BroadsignClient(Contract::getConnectionConfig()), $clientId);

            if ($customer !== null) {
                $client = Client::query()->create([
                    "broadsign_customer_id" => $clientId,
                    "name"                  => $customer->name]);
            } else {
                throw new InvalidArgumentException("Invalid value for client_id");
            }
        }


        $contract = new Contract([
            "contract_id" => $contractId,
            "client_id"   => $client->id,
            "owner_id"    => Auth::id(),
            "data"        => []
        ]);
        $contract->save();

        RefreshContractReservations::dispatch($contract->id);

        return new Response($contract, 201);
    }

    public function show(ShowContractRequest $request, Contract $contract) {
        $with = $request->get("with", []);
        if (in_array("client", $with, true)) {
            $contract->load("client");
        }

        if (in_array("reservations", $with, true)) {
            $contract->load("reservations");
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

        if (in_array("data", $with, true)) {
            $contract->load("data");
        }

        return new Response($contract);
    }

    public function refresh(RefreshContractRequest $request, Contract $contract) {
        RefreshContractReservations::dispatchSync($contract->id);

        return new Response(["status" => "ok"]);
    }

    public function destroy(DestroyContractRequest $request, Contract $contract) {
        $contract->delete();
    }
}
