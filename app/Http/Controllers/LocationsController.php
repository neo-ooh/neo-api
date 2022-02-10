<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LocationsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Neo\Enums\Capability;
use Neo\Exceptions\UnsupportedBroadcasterOptionException;
use Neo\Http\Requests\Campaigns\SetScreensStateRequest;
use Neo\Http\Requests\Locations\ForceRefreshPlaylistRequest;
use Neo\Http\Requests\Locations\ListLocationsRequest;
use Neo\Http\Requests\Locations\SalesLocationRequest;
use Neo\Http\Requests\Locations\SearchLocationsRequest;
use Neo\Http\Requests\Locations\ShowLocationRequest;
use Neo\Http\Requests\Locations\UpdateLocationRequest;
use Neo\Models\Actor;
use Neo\Models\Format;
use Neo\Models\Location;
use Neo\Models\Network;
use Neo\Models\Player;
use Neo\Services\Broadcast\Broadcast;
use Neo\Services\Broadcast\Broadcaster;
use Neo\Services\Broadcast\BroadSign\API\BroadsignClient;
use Neo\Services\Broadcast\BroadSign\BroadSignConfig;

class LocationsController extends Controller {
    /**
     * List all locations this user has access to
     *
     * @param ListLocationsRequest $request
     *
     * @return Response
     */
    public function index(ListLocationsRequest $request): Response {
        /** @var Actor $actor */
        $actor = Auth::user();

        if (!$actor->hasCapability(Capability::locations_edit())) {
            Redirect::route('actors.locations', ['actor' => Auth::user()]);
        }

        $query = Location::query()->with(["display_type"])->orderBy("name");

        // Should we  scope by network ?
        $query->when($request->has("network_id"), function (Builder $query) use ($request) {
            $query->where("network_id", "=", $request->input("network_id"));
        });

        // Should we scope by format ?
        $query->when($request->has("format_id"), function (Builder $query) use ($request) {
            $displayTypes = Format::query()->find($request->input("format_id"))->display_types->pluck("id");
            $query->whereIn("display_type_id", $displayTypes);
        });

        $loadHierarchy = in_array('hierarchy', $request->input('with', []), true) ?? $request->has('with_hierarchy');

        if ($loadHierarchy) {
            $query->with(["container"]);
        }

        $locations = $query->get()->values();

        if ($loadHierarchy) {
            $locations->each(fn($location) => $location->loadHierarchy());
        }

        return new Response($locations);
    }

    public function search(SearchLocationsRequest $request) {
        $q = strtolower($request->query("q", ""));

        // We allow search with empty string only when an actor is provided.
        if (($q === '') && !$request->has("actor")) {
            return new Response([]);
        }

        $locations = Location::query()
                             ->with("network")
                             ->when($request->has("network"), function (Builder $query) use ($request) {
                                 $query->where("network_id", "=", $request->input("network"));
                             })->when($request->has("format"), function (Builder $query) use ($request) {
                $query->whereHas("display_type.formats", function (Builder $query) use ($request) {
                    $query->where("id", "=", $request->input("format"));
                });
            })->when($request->has("actor"), function (Builder $query) use ($request) {
                $query->whereHas("actors", function (Builder $query) use ($request) {
                    $query->where("id", "=", $request->input("actor"));
                });
            })
                             ->where('locations.name', 'LIKE', "%$q%")
                             ->get();

        return new Response($locations);
    }

    /**
     * @param SalesLocationRequest $request
     * @return Response
     * @return Response
     */
    public function allByNetwork(SalesLocationRequest $request) {
        // We want to list all the locations per network and per province
        // retrieve our networks roots
        $locations = [];

        $networks = Network::query()->whereHas("broadcaster_connection", function (Builder $query) {
            $query->where("broadcaster", "=", Broadcaster::BROADSIGN);
        })->get();

        /** @var Network $network */
        foreach ($networks as $network) {
            $locations[$network->id] = $network->locations
                ->map(fn($location) => [
                    "id"       => $location->id,
                    "name"     => $location->name,
                    "province" => $location->province,
                    "city"     => $location->city,
                ])
                ->groupBy(["province", "city"]);
        }

        return new Response($locations);
    }

    /**
     * @param ShowLocationRequest $request
     * @param Location            $location
     * @return Response
     */
    public function show(ShowLocationRequest $request, Location $location): Response {
        $relations = $request->input("with", []);

        if (in_array("network", $relations)) {
            $location->load("network");
        }

        if (in_array("broadcaster", $relations)) {
            $location->load("network.broadcaster_connection");
        }

        if (in_array("players", $relations)) {
            $location->load("players");
        }

        if (in_array("actors", $relations)) {
            $location->load("actors");
        }

        if (in_array("products", $relations)) {
            $location->load(["products",
                             "products.category",
                             "products.locations",
                             "products.impressions_models"]);
        }

        return new Response($location);
    }

    /**
     * @param UpdateLocationRequest $request
     * @param Location              $location
     * @return Response
     */
    public function update(UpdateLocationRequest $request, Location $location): Response {
        $location->name            = $request->input('name');
        $location->scheduled_sleep = $request->input("scheduled_sleep");
        $location->sleep_end       = $request->input("sleep_end");
        $location->sleep_start     = $request->input("sleep_start");
        $location->save();

        if ($location->network()->first()->broadcaster_connection->broadcaster === 'pisignage') {
            $network = Broadcast::network($location->network_id);
            $network->updateLocation($location->id);
        }

        return new Response($location->load('display_type'));
    }

    public function setScreensState(SetScreensStateRequest $request, Location $location) {
        //Make sure the location supports screen controls
        if ($location->network->broadcaster_connection->broadcaster !== Broadcaster::PISIGNAGE) {
            throw new UnsupportedBroadcasterOptionException("{$location->network->broadcaster_connection->broadcaster} does not support the 'screen_controls option'");
        }

        // Get a network instance
        $broadcaster = Broadcast::network($location->network_id);

        $state = $request->input("state");

        // Send the updated screen state to each location
        /** @var Player $player */
        foreach ($location->players as $player) {
            $broadcaster->setScreenState($player->external_id, $state);
        }
    }

    public function _forceRefreshPlaylist(ForceRefreshPlaylistRequest $request, Location $location) {
        //Make sure the location supports screen controls
        $config = Broadcast::network($location->network_id)->getConfig();
        if (!($config instanceof BroadSignConfig)) {
            throw new UnsupportedBroadcasterOptionException("$location->name does not support playlist force refresh");
        }

        $client = new BroadsignClient($config);

        /** @var Player $player */
        foreach ($location->players as $player) {
            (new \Neo\Services\Broadcast\BroadSign\Models\Player($client, ["id" => $player->external_id]))->forceUpdatePlaylist();
        }
    }
}
