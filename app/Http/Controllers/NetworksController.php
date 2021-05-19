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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Neo\Http\Requests\Networks\DestroyNetworkRequest;
use Neo\Http\Requests\Networks\ListNetworksRequest;
use Neo\Http\Requests\Networks\ShowNetworkRequest;
use Neo\Http\Requests\Networks\StoreNetworkRequest;
use Neo\Http\Requests\Networks\UpdateNetworkRequest;
use Neo\Models\BroadcasterConnection;
use Neo\Models\Network;
use Neo\Models\NetworkSettingsBroadSign;
use Neo\Models\NetworkSettingsPiSignage;
use Neo\Services\Broadcast\Broadcaster;
use function Ramsey\Uuid\v4;

class NetworksController extends Controller {
    public function index(ListNetworksRequest $request) {

        $query = Network::query()->orderBy('name');

        $query->when($request->has("with") && in_array("connection", $request->input("with"), true), function ($query) {
            $query->with("broadcaster_connection");
        });

        $networks = $query->get();

        if($request->has("with") && in_array("settings", $request->input("with"), true)) {
            $networks->append("settings");
        }

        return new Response($networks);
    }

    public function store(StoreNetworkRequest $request) {
        $network = new Network();
        $network->uuid = v4();
        $network->name = $request->input("name");
        $network->connection_id = $request->input("connection_id");
        $network->save();

        if($network->broadcaster_connection->broadcaster === Broadcaster::BROADSIGN) {
            $settings = new NetworkSettingsBroadSign();
            $settings->container_id = $request->input("container_id");
            $settings->customer_id = $request->input("customer_id");
            $settings->tracking_id = $request->input("tracking_id");
        } else { // if ($network->broadcaster_connection->broadcaster === Broadcaster::PISIGNAGE)
            $settings = new NetworkSettingsPiSignage();
        }

        $settings->network_id = $network->id;
        $settings->save();

        return new Response(["id" => $network->id], 201);
    }

    public function show(ShowNetworkRequest $request, Network $network) {
        return new Response($network->load(["settings", "broadcaster_connection"]));
    }

    public function update(UpdateNetworkRequest $request, Network $network) {
        $network->name = $request->input("name");
        $network->save();

        if($network->broadcaster_connection->broadcaster === Broadcaster::BROADSIGN) {
            $settings = $network->settings;
            $settings->container_id = $request->input("container_id");
            $settings->customer_id = $request->input("customer_id");
            $settings->tracking_id = $request->input("tracking_id");
            $settings->save();
        }

        return new Response($network->load(["broadcaster_connection"])->append("settings"));
    }

    public function destroy(DestroyNetworkRequest $request, Network $network) {
        $network->delete();

        return new Response(["result" => "ok"], 200);
    }
}
