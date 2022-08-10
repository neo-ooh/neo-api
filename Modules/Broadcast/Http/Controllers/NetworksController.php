<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NetworksController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\Networks\DestroyNetworkRequest;
use Neo\Modules\Broadcast\Http\Requests\Networks\ListNetworksRequest;
use Neo\Modules\Broadcast\Http\Requests\Networks\ShowNetworkRequest;
use Neo\Modules\Broadcast\Http\Requests\Networks\StoreNetworkRequest;
use Neo\Modules\Broadcast\Http\Requests\Networks\SynchronizeNetworkRequest;
use Neo\Modules\Broadcast\Http\Requests\Networks\UpdateNetworkRequest;
use Neo\Modules\Broadcast\Jobs\Networks\SynchronizeNetworkJob;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Broadcast\Models\StructuredColumns\NetworkSettings;
use Neo\Modules\Broadcast\Services\BroadcasterType;
use function Ramsey\Uuid\v4;

class NetworksController extends Controller {
    public function index(ListNetworksRequest $request): Response {
        $networks = Network::query()->get();

        $networks->each(fn(Network $network) => $network->withPublicRelations($request->input("with", [])));

        return new Response($networks);
    }

    public function store(StoreNetworkRequest $request): Response {
        $network                = new Network();
        $network->uuid          = v4();
        $network->connection_id = $request->input("connection_id");
        $network->name          = $request->input("name");
        $network->color         = $request->input("color");

        $settings = $this->applySettings($request, $network, new NetworkSettings());

        $network->settings = $settings;
        $network->save();

        // trigger an update of the network to populate the new one
        SynchronizeNetworkJob::dispatch($network->getKey());

        return new Response(["id" => $network->id], 201);
    }

    public function show(ShowNetworkRequest $request, Network $network): Response {
        return new Response($network->withPublicRelations($request->input("with", [])));
    }

    public function update(UpdateNetworkRequest $request, Network $network): Response {
        $network->name  = $request->input("name");
        $network->color = $request->input("color");

        $settings = $this->applySettings($request, $network, $network->settings);

        $network->settings = $settings;
        $network->save();

        return new Response($network->load(["broadcaster_connection"])->makeVisible("settings"));
    }

    protected function applySettings(FormRequest $request, Network $network, NetworkSettings $settings) {
        switch ($network->broadcaster_connection->broadcaster) {
            case BroadcasterType::BroadSign:
                $settings->customer_id            = $request->input("customer_id");
                $settings->root_container_id      = $request->input("customer_id");
                $settings->campaigns_container_id = $request->input("campaigns_container_id");
                $settings->creatives_container_id = $request->input("creatives_container_id");
                break;
            case BroadcasterType::PiSignage:
                break;
            case BroadcasterType::SignageOS:
        }

        return $settings;
    }

    public function destroy(DestroyNetworkRequest $request, Network $network): Response {
        $network->delete();

        return new Response(["result" => "ok"], 200);
    }

    public function synchronize(SynchronizeNetworkRequest $request, Network $network): Response {
        SynchronizeNetworkJob::dispatch($network->getKey());

        return new Response(["status" => "ok"]);
    }
}
