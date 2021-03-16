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
use Neo\Http\Requests\Locations\ListLocationsRequest;
use Neo\Http\Requests\Locations\SalesLocationRequest;
use Neo\Http\Requests\Locations\SearchLocationsRequest;
use Neo\Http\Requests\Locations\ShowLocationRequest;
use Neo\Http\Requests\Locations\UpdateLocationRequest;
use Neo\Models\Actor;
use Neo\Models\Format;
use Neo\Models\Location;
use Neo\Models\Param;

class LocationsController extends Controller {
    /**
     * List all locations this user has access to
     *
     * @param SearchLocationsRequest $request
     *
     * @return Response
     */
    public function index (ListLocationsRequest $request): Response {
        /** @var Actor $actor */
        $actor = Auth::user();

        if (!$actor->hasCapability(Capability::locations_edit())) {
            Redirect::route('actors.locations', ['actor' => Auth::user()]);
        }

        $query = Location::query()->with(["display_type"])->orderBy("name");

        // Should we  scope by container ?
        $query->when($request->has("container"), function (Builder $query) use ($request) {
            $query->where("container_id", "=", $request->input("container"));
        });

        // Should we scope by format ?
        $query->when($request->has("format"), function (Builder $query) use ($request) {
            $displayTypes = Format::find($request->input("format"))->display_types->pluck("id");
            $query->whereIn("display_type_id", $displayTypes);
        });

        $loadHierarchy = in_array('hierarchy', $request->input('with', []), true) ?? $request->has('with_hierarchy');

        if ($loadHierarchy) {
            $query->with(["container"]);
        }
        clock()->event('Executing and serializing')->color('purple')->begin();
        $locations = $query->get()->values();
        clock()->event('Executing and serializing')->end();

        if ($loadHierarchy) {
            $locations->each(fn($location) => $location->loadHierarchy());
        }

        return new Response($locations);
    }

    public function search(SearchLocationsRequest $request) {
        $q         = strtolower($request->query("q"));
        $locations = Location::query()
                             ->with("display_type")
                             ->where('locations.name', 'LIKE', "%{$q}%")
                             ->get();

        return new Response($locations);
    }

    /**
     * @param SalesLocationRequest $request
     */
    public function allByNetworks(SalesLocationRequest $request) {
        // We want to list all the locations per network and per province
        // retrieve our networks roots
        $locations = [];

        foreach (["NETWORK_SHOPPING", "NETWORK_FITNESS", "NETWORK_OTG"] as $networkID) {
            $root = Actor::find(Param::find($networkID)->value);

            if(!$root) {
                // Ignore network if root is missing
                continue;
            }

            $locations[$networkID] = $root->getLocations(true, false, true, true)->groupBy("province");
        }

        return new Response($locations);
    }

    /**
     * @param ShowLocationRequest $request
     * @param Location            $location
     * @return Response
     */
    public function show(ShowLocationRequest $request, Location $location): Response {
        if ($request->has('with_hierarchy')) {
            $location->loadHierarchy();
        }

        if ($request->has('with_players')) {
            $location->load('players');
        }

        if ($request->has('with_bursts')) {
            $location->load('bursts');
        }

        if ($request->has('with_reports')) {
            $location->load('reports');
        }

        return new Response($location);
    }

    /**
     * @param UpdateLocationRequest $request
     * @param Location $location
     * @return Response
     */
    public function update (UpdateLocationRequest $request, Location $location): Response {
        $location->name = $request->input('name');
        $location->save();

        return new Response($location->load('format'));
    }
}
