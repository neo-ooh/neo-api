<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterConnectionsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\BroadcasterConnections\DestroyConnectionRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcasterConnections\ListConnectionRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcasterConnections\ListConnectionsRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcasterConnections\ShowConnectionRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcasterConnections\StoreConnectionRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcasterConnections\UpdateConnectionRequest;
use Neo\Modules\Broadcast\Models\BroadcasterConnection;
use Neo\Modules\Broadcast\Models\StructuredColumns\BroadcasterSettings;
use Neo\Modules\Broadcast\Services\BroadcasterType;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use function Ramsey\Uuid\v4;

class BroadcasterConnectionsController extends Controller {
    public function index(ListConnectionsRequest $request): Response {
        return new Response(BroadcasterConnection::query()
                                                 ->orderBy("name")
                                                 ->get());
    }

    public function store(StoreConnectionRequest $request): Response {
        $type = BroadcasterType::from($request->input("type"));

        $connection              = new BroadcasterConnection();
        $connection->uuid        = v4();
        $connection->name        = $request->input("name");
        $connection->broadcaster = $type;

        $settings = new BroadcasterSettings();

        // Set up settings for the connection depending on the provider
        switch ($type) {
            case BroadcasterType::BroadSign:
                $settings->domain_id   = $request->input("domain_id");
                $settings->customer_id = $request->input("customer_id", null);
                break;
            case BroadcasterType::PiSignage:
                $settings->server_url = $request->input("server_url");
                $settings->token      = $request->input("token");
                break;
            case BroadcasterType::SignageOS:
                break;
        }

        $connection->settings = $settings;
        $connection->save();
        $connection->refresh();

        if ($type === BroadcasterType::BroadSign) {
            // Store the BroadSign certificate
            $cert = $request->file("certificate");

            if (!$cert->isValid()) {
                throw new UploadException($cert->getErrorMessage(), $cert->getError());
            }

            $connection->storeCertificate($cert);
        }

        // We are good, return the ID of the created resource
        return new Response(["id" => $connection->id], 201);
    }

    public function show(ShowConnectionRequest $request, BroadcasterConnection $connection): Response {
        return new Response($connection->load(["settings"]));
    }

    public function update(UpdateConnectionRequest $request, BroadcasterConnection $connection): Response {
        $connection->name   = $request->input("name");
        $connectionSettings = $connection->settings;

        switch ($connection->broadcaster) {
            case BroadcasterType::BroadSign:
                $connectionSettings->domain_id   = $request->input("domain_id");
                $connectionSettings->customer_id = $request->input("customer_id");
                break;
            case BroadcasterType::PiSignage:
                $connectionSettings->server_url = $request->input("server_url");
                $connectionSettings->token      = $request->input("token", $connectionSettings->token);
                break;
            case BroadcasterType::SignageOS:
                break;
        }

        $connection->settings = $connectionSettings;
        $connection->save();

        // Update connection certificate if applicable
        if ($connection->broadcaster === BroadcasterType::BroadSign && $request->hasFile("certificate")) {
            $cert = $request->file("certificate");

            if (!$cert->isValid()) {
                throw new UploadException($cert->getErrorMessage(), $cert->getError());
            }

            $connection->storeCertificate($cert);
        }

        return new Response($connection->makeVisible("settings"));
    }

    public function destroy(DestroyConnectionRequest $request, BroadcasterConnection $connection): Response {
        $connection->delete();

        return new Response();
    }
}
