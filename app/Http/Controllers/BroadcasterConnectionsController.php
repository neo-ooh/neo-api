<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use InvalidArgumentException;
use Neo\Http\Requests\BroadcasterConnections\DestroyConnectionRequest;
use Neo\Http\Requests\BroadcasterConnections\ListConnectionRequest;
use Neo\Http\Requests\BroadcasterConnections\StoreConnectionRequest;
use Neo\Http\Requests\BroadcasterConnections\UpdateConnectionRequest;
use Neo\Models\BroadcasterConnection;
use Neo\Models\Casts\ConnectionSettingsBroadSign;
use Neo\Models\Casts\ConnectionSettingsPiSignage;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use function Ramsey\Uuid\v4;

class BroadcasterConnectionsController extends Controller {
    public function index(): Response {
        return new Response(BroadcasterConnection::query()->orderBy("name")->get()->makeVisible("settings"));
    }

    public function store(StoreConnectionRequest $request): Response {
        $name = $request->input("name");
        $type = $request->input("type");

        $connection              = new BroadcasterConnection();
        $connection->uuid        = v4();
        $connection->name        = $name;
        $connection->broadcaster = $type;
        $connection->save();

        // Set up settings for the connection depending on the provider
        switch ($type) {
            case "broadsign":
                $settings = new ConnectionSettingsBroadSign([
                    "domain_id"           => $request->input("domain_id"),
                    "default_customer_id" => $request->input("default_customer_id"),
                    "default_tracking_id" => $request->input("default_tracking_id"),
                ]);
                break;
            case "pisignage":
                $settings = new ConnectionSettingsPiSignage([
                    "server_url" => $request->input("server_url"),
                    "token"      => $request->input("token"),

                ]);
                break;
            case "odoo":
                break;
        }

        $connection->settings = $settings;
        $connection->save();
        $connection->refresh();

        if ($type === 'broadsign') {
            // Store the broadsign certificate
            $cert = $request->file("certificate");

            if (!$cert->isValid()) {
                throw new UploadException($cert->getErrorMessage(), $cert->getError());
            }

            // !! IMPORTANT !! Visibility has to be set to private, this key has no password
            // The key is stored on the shared storage to be accessible by all the API nodes
            $cert->storeAs($connection->settings->certificate_path, $connection->settings->file_name, ["visibility" => "private"]);
        }

        // We are good, return the ID of the created resource
        return new Response(["id" => $connection->id], 201);
    }

    public function show(ListConnectionRequest $request, BroadcasterConnection $connection): Response {
        return new Response($connection->load(["settings"]));
    }

    public function update(UpdateConnectionRequest $request, BroadcasterConnection $connection): Response {
        $type = $request->input("type");

        if ($connection->broadcaster !== $type) {
            throw new InvalidArgumentException("Invalid connection type for the specified connection.");
        }

        $connection->name = $request->input("name");
        $connection->save();

        $connectionSettings = $connection->settings;

        if ($connection->broadcaster === 'broadsign') {
            if ($request->hasFile("certificate")) {
                $cert = $request->file("certificate");

                if (!$cert->isValid()) {
                    throw new UploadException($cert->getErrorMessage(), $cert->getError());
                }

                $cert->storeAs($connectionSettings->certificate_path, $connectionSettings->file_name, ["visibility" => "private"]);
            }

            $connectionSettings->domain_id           = $request->input("domain_id");
            $connectionSettings->default_customer_id = $request->input("default_customer_id");
            $connectionSettings->default_tracking_id = $request->input("default_tracking_id");
        } else { // if ($connection->broadcaster === 'pisignage')
            $connectionSettings->token = $request->input("token", $connectionSettings->token);
        }

        $connection->settings = $connectionSettings;
        $connection->save();

        return new Response($connection->makeVisible("settings"));
    }

    public function destroy(DestroyConnectionRequest $request, BroadcasterConnection $connection): Response {
        $connection->delete(); // Soft delete ;)

        return new Response();
    }
}
