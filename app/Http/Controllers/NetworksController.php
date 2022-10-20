<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworksController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Neo\Exceptions\InvalidBroadcastServiceException;
use Neo\Http\Requests\Networks\DestroyNetworkRequest;
use Neo\Http\Requests\Networks\ListNetworksByIdsRequest;
use Neo\Http\Requests\Networks\ListNetworksRequest;
use Neo\Http\Requests\Networks\ShowNetworkRequest;
use Neo\Http\Requests\Networks\StoreNetworkRequest;
use Neo\Http\Requests\Networks\UpdateNetworkRequest;
use Neo\Models\Actor;
use Neo\Models\Network;
use Neo\Models\UnstructuredData\NetworkSettingsBroadSign;
use Neo\Models\UnstructuredData\NetworkSettingsPiSignage;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\Broadcaster;
use function Ramsey\Uuid\v4;

class NetworksController extends Controller {
    public function index(ListNetworksRequest $request): Response {
        $query = Network::query()->orderBy('name');

        // If an actor is specified, we only return network accessible by the actor through its associated locations
        $query->when($request->has("actor"), function (Builder $query) use ($request) {
            $query->whereIn("id", Actor::find($request->input('actor'))
                                       ->getLocations(true, true, true, true)
                                       ->pluck('network_id'));
        });

        $query->when($request->has("with") && in_array("connection", $request->input("with"), true), function ($query) {
            $query->with("broadcaster_connection");
        });

        $query->when($request->has("with") && in_array("fields", $request->input("with"), true), function ($query) {
            $query->with("properties_fields");
        });

        $networks = $query->get();

        if ($request->has("with") && in_array("settings", $request->input("with"), true)) {
            $networks->makeVisible("settings");
        }

        return new Response($networks);
    }

    public function byIds(ListNetworksByIdsRequest $request): Response {
        $query = Network::query()->orderBy('name');

        $query->when($request->has("with") && in_array("connection", $request->input("with"), true), function ($query) {
            $query->with("broadcaster_connection");
        });

        $query->when($request->has("with") && in_array("fields", $request->input("with"), true), function ($query) {
            $query->with("properties_fields");
        });

        $networks = $query->findMany($request->input("ids"));

        if ($request->has("with") && in_array("settings", $request->input("with"), true)) {
            $networks->makeVisible("settings");
        }

        return new Response($networks);
    }

    /**
     * @throws InvalidBroadcastServiceException
     */
    public function store(StoreNetworkRequest $request): Response {
        $network                = new Network();
        $network->uuid          = v4();
        $network->name          = $request->input("name");
        $network->color         = $request->input("color");
        $network->connection_id = $request->input("connection_id");

        $settings = match ($network->broadcaster_connection->broadcaster) {
            Broadcaster::BROADSIGN => new NetworkSettingsBroadSign([
                "container_id"              => $request->input("container_id"),
                "customer_id"               => $request->input("customer_id"),
                "tracking_id"               => $request->input("tracking_id"),
                "reservations_container_id" => $request->input("reservations_container_id"),
                "ad_copies_container_id"    => $request->input("ad_copies_container_id"),
            ]),
            Broadcaster::PISIGNAGE => new NetworkSettingsPiSignage([]),
            default                => null,
        };

        $network->settings = $settings;
        $network->save();

        // trigger an update of the network to populate the new one
        $broadcastNetwork = Broadcast::network($network->id);
        $broadcastNetwork->synchronizePlayers();
        $broadcastNetwork->synchronizeLocations();

        return new Response(["id" => $network->id], 201);
    }

    public function show(ShowNetworkRequest $request, Network $network): Response {
        if (in_array("fields", $request->input("with", []))) {
            $network->load("properties_fields");
        }

        return new Response($network->load(["broadcaster_connection"])->makeVisible(["settings"]));
    }

    public function update(UpdateNetworkRequest $request, Network $network): Response {
        $network->name  = $request->input("name");
        $network->color = $request->input("color");
        $settings       = $network->settings;

        if ($network->broadcaster_connection->broadcaster === Broadcaster::BROADSIGN) {
            $settings->container_id              = $request->input("container_id");
            $settings->customer_id               = $request->input("customer_id");
            $settings->tracking_id               = $request->input("tracking_id");
            $settings->reservations_container_id = $request->input("reservations_container_id");
            $settings->ad_copies_container_id    = $request->input("ad_copies_container_id");
        }

        $network->settings = $settings;
        $network->save();

        return new Response($network->load(["broadcaster_connection"])->makeVisible("settings"));
    }

    public function destroy(DestroyNetworkRequest $request, Network $network): Response {
        $network->delete();

        return new Response(["result" => "ok"], 200);
    }


    public function refresh(): Response {
        Artisan::queue("network:sync");

        return new Response(["status" => "ok"]);
    }
}
