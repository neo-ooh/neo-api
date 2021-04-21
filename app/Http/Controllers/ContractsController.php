<?php

namespace Neo\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use Neo\Http\Requests\Contracts\ShowContractRequest;
use Neo\Models\Contract;

class ContractsController extends Controller
{
    public function store() {}
    public function show(ShowContractRequest $request, Contract $contract) {
        $with = $request->get("with", []);
        if (in_array("client", $with, true)) {
            $contract->load("client");
        }

        if (in_array("reservations", $with, true)) {
            $contract->load("reservations");
        }

        if (in_array("bursts", $with, true)) {
            $contract->load("bursts", "bursts.screenshots", "bursts.location");
        }

        return new \Illuminate\Http\Response($contract);
    }
    public function update() {}
}
