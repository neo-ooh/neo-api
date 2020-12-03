<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Neo\Enums\Capability;
use Neo\Http\Requests\Locations\ListLocationsRequest;
use Neo\Http\Requests\Locations\ShowLocationRequest;
use Neo\Http\Requests\Locations\UpdateLocationRequest;
use Neo\Models\Location;

class LocationsController extends Controller {
    /**
     * List all locations this user has access to
     *
     * @param ListLocationsRequest $request
     *
     * @return Response
     */
    public function index (ListLocationsRequest $request): Response {
        $actor = Auth::user();

        if (!$actor->hasCapability(Capability::locations_edit())) {
            Redirect::route('actors.locations', [ 'actor' => Auth::user() ]);
        }

        $query = Location::query();

        if ($request->has("format")) {
            $query->where("format_id", "=", $request->input("format"));
        }

        $loadHierarchy = in_array('hierarchy', $request->input('with', []), true) ?? $request->has('with_hierarchy');

        if ($loadHierarchy) {
            $query->with([ 'container' ]);
        }

        $query->orderBy("name");
        $locations = $query->get()->values();

        if ($loadHierarchy) {
            $locations->each(fn(location $location) => $location->container->append("parents_list"));
        }

        if ($loadHierarchy) {
            $locations->each(fn ($location) => $location->loadHierarchy());
        }

        return new Response($locations);
    }

    /**
     * @param ShowLocationRequest $request
     * @param Location $location
     * @return Response
     */
    public function show (ShowLocationRequest $request, Location $location): Response {
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
