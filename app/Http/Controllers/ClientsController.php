<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ClientsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Requests\Clients\ListClientsByIdsRequest;
use Neo\Http\Requests\Clients\ListClientsRequest;
use Neo\Http\Requests\Clients\ShowClientRequest;
use Neo\Modules\Properties\Models\Client;

class ClientsController extends Controller {
	public function index(ListClientsRequest $request) {
		// If the user has the contracts_manage capability, we list all clients, otherwise we limit results to clients with a contract associated to the current user.
		$query = Client::query();

		/** @var array $with */
		$with = $request->input("with", []);

		$clients = $query->when(in_array("contracts", $with, true), function (Builder $query) {
			$query->with("contracts");

			if (!Gate::allows(Capability::contracts_manage->value)) {
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
