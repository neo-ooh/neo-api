<?php

namespace Neo\Http\Controllers;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Neo\Enums\Capability;
use Neo\Http\Requests\Clients\ListClientsByIdsRequest;
use Neo\Http\Requests\Clients\ListClientsRequest;
use Neo\Http\Requests\Clients\ShowClientRequest;
use Neo\Models\Client;
use Neo\Models\Contract;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\Models\Customer;

class ClientsController extends Controller {
    public function index(ListClientsRequest $request) {
        if ($request->input("distant", false)) {
            $config          = Contract::getConnectionConfig();
            $broadsignClient = new BroadsignClient($config);

            $clients = Customer::all($broadsignClient);

            return new Response($clients
                ->filter(fn($client) => !Str::startsWith($client->name, ["~", "*"]))
                ->map(fn($client) => [
                    "id"                    => $client->id,
                    "broadsign_customer_id" => $client->id,
                    "name"                  => $client->name
                ])
                ->sortBy("name")
                ->values()
                ->toArray());
        }

        // If the user has the contracts_manage capability, we list all clients, otherwise we limit results to clients with a contract associated to the current user.
        $query = Client::query();


        /** @var array $with */
        $with = $request->input("with", []);

        $clients = $query->when(in_array("contracts", $with, true), function (Builder $query) {
            $query->with("contracts");

            if (!Gate::allows(Capability::contracts_manage)) {
                $query->whereHas("contracts", function (Builder $query) {
                    $query->where("owner_id", "=", Auth::id());
                });
            }
        })
                         ->orderBy("name")
                         ->get();

        return new Response($clients->values());
    }

    public function byId(ListClientsByIdsRequest $request) {
        return new Response(Client::query()->whereIn("id", $request->input("ids"))->orderBy("name")->get());
    }

    public function store() {

    }

    public function show(ShowClientRequest $request, Client $client) {
        return new Response($client->load(["contracts"]));
    }

    public function update() {
    }
}
