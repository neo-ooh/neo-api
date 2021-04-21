<?php

namespace Neo\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Requests\Clients\ListClientsRequest;
use Neo\Http\Requests\Clients\ShowClientRequest;
use Neo\Models\Client;

class ClientsController extends Controller {
    public function index(ListClientsRequest $request) {
        // If the user has the contracts_manage capability, we list all clients, otherwise we limit results to clients with a contract associated to the current user.
        if (Gate::allows(Capability::contracts_manage)) {
            $query = Client::query();
        } else {
            $query = Client::query()->whereHas("contracts", function (Builder $query) {
                $query->where("owner_id", "=", Auth::id());
            });
        }

        /** @var array $with */
        $with    = $request->input("with", []);

        $clients = $query->when(in_array("contracts", $with, true), function(Builder $query) {
            $query->with("contracts");
        })
                         ->orderBy("name")
                         ->get();

        return new Response($clients->values());
    }

    public function store() {

    }

    public function show(ShowClientRequest $request, Client $client) {
        return new Response($client->load(["contracts"]));
    }

    public function update() {
    }
}
