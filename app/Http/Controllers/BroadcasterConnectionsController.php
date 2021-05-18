<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use InvalidArgumentException;
use Neo\Http\Requests\BroadcasterConnections\DestroyConnectionRequest;
use Neo\Http\Requests\BroadcasterConnections\ListConnectionRequest;
use Neo\Http\Requests\BroadcasterConnections\StoreConnectionRequest;
use Neo\Http\Requests\BroadcasterConnections\UpdateConnectionRequest;
use Neo\Models\BroadcasterConnection;
use Neo\Models\ConnectionSettingsBroadSign;
use Neo\Models\ConnectionSettingsPiSignage;
use Storage;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use function Ramsey\Uuid\v4;

class BroadcasterConnectionsController extends Controller {
    public function index(): Response {
        return new Response(BroadcasterConnection::all()->sortBy("name"));
    }

    public function store(StoreConnectionRequest $request): Response {
        $name = $request->input("name");
        $type = $request->input("type");

        $connection              = new BroadcasterConnection();
        $connection->uuid = v4();
        $connection->name        = $name;
        $connection->broadcaster = $type;
        $connection->save();
        $connection->refresh();

        // Set up settings for the connection depending on the provider
        if ($type === 'broadsign') {
            $settings                      = new ConnectionSettingsBroadSign();
            $settings->domain_id           = $request->input("domain_id");
            $settings->default_customer_id = $request->input("default_customer_id");
            $settings->default_tracking_id = $request->input("default_tracking_id");
        } else { // if ($type === 'pisignage')
            $settings             = new ConnectionSettingsPiSignage();
            $settings->server_url = $request->input("server_url");
            $settings->token      = $request->input("token");
        }

        $settings->connection_id = $connection->id;
        $settings->save();
        $settings->refresh();

        if ($type === 'broadsign') {
            // Store the broadsign certificate
            $cert = $request->file("certificate");

            if (!$cert->isValid()) {
                throw new UploadException($cert->getErrorMessage(), $cert->getError());
            }

            // !! IMPORTANT !! Visibility has to be set to private, this key has no password
            // The key is stored on the shared storage to be accessible by all the API nodes
            Storage::putFile($settings->certificate_path, $cert, ["visibility" => "private"]);
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

        if ($type === 'broadsign') {
            $connection->settings->domain_id           = $request->input("domain_id");
            $connection->settings->default_customer_id = $request->input("default_customer_id");
            $connection->settings->default_tracking_id = $request->input("default_tracking_id_id");

            if ($request->hasFile("certificate")) {
                $cert = $request->file("certificate");

                if (!$cert->isValid()) {
                    throw new UploadException($cert->getErrorMessage(), $cert->getError());
                }

                Storage::putFile($connection->settings->certificate_path, $cert, ["visibility" => "private"]);
            }
        } else { // if ($type === 'pisignage')
            $connection->settings->token = $request->input("token", $connection->settings->token);
        }

        $connection->settings->save();

        return new Response($connection->load("settings"));
    }

    public function destroy(DestroyConnectionRequest $request, BroadcasterConnection $connection): Response {
        $connection->delete(); // Soft delete ;)

        return new Response();
    }
}
