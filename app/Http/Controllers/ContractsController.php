<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\Contracts\ShowContractRequest;
use Neo\Http\Requests\Contracts\StoreContractRequest;
use Neo\Jobs\RefreshContractsReservations;
use Neo\Models\Client;
use Neo\Models\Contract;

class ContractsController extends Controller {
    public function store(StoreContractRequest $request) {
        $contractId = $request->input("contract_id");
        $clientId = $request->input("client_id");

        $contract = new Contract([
            "contract_id" => strtoupper($contractId),
            "client_id" => $clientId,
            "owner_id" => Auth::id(),
        ]);
        $contract->save();

        RefreshContractsReservations::dispatch($contract->id);

        return app()->call(static::class, [
            "with" => ["reservations", "bursts"]
        ]);
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

        return new Response($contract);
    }

    public function update() {
    }
}
