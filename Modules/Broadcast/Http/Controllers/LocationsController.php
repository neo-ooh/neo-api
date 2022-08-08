<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LocationsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Neo\Enums\Capability;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Exceptions\InvalidBroadcasterAdapterException;
use Neo\Modules\Broadcast\Http\Requests\Locations\ListLocationsRequest;
use Neo\Modules\Broadcast\Http\Requests\Locations\SearchLocationsRequest;
use Neo\Modules\Broadcast\Http\Requests\Locations\ShowLocationRequest;
use Neo\Modules\Broadcast\Http\Requests\Locations\UpdateLocationRequest;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\Location;
use Neo\Modules\Broadcast\Services\BroadcasterAdapterFactory;
use Neo\Modules\Broadcast\Services\BroadcasterCapability;
use Neo\Modules\Broadcast\Services\BroadcasterLocationsSleepSimple;
use Neo\Modules\Broadcast\Services\BroadcasterOperator;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class LocationsController extends Controller {
    /**
     * List all locations this user has access to
     *
     * @param ListLocationsRequest $request
     *
     * @return SymfonyResponse
     */
    public function index(ListLocationsRequest $request): SymfonyResponse {
        // First thing is to check if the current user has access to all locations, or just the ones in its hierarchy
        /** @var Actor $actor */
        $actor = Auth::user();

        if (!$actor->hasCapability(Capability::locations_edit)) {
            return Redirect::route('actors.locations', ['actor' => Auth::user()]);
        }

        $query = Location::query()->orderBy("name");

        // Should we  scope by network ?
        $query->when($request->has("network_id"), function (Builder $query) use ($request) {
            $query->where("network_id", "=", $request->input("network_id"));
        });

        // Should we scope by format ?
        $query->when($request->has("format_id"), function (Builder $query) use ($request) {
            $displayTypes = Format::query()->find($request->input("format_id"))->display_types->pluck("id");
            $query->whereIn("display_type_id", $displayTypes);
        });

        /** @var Collection<Location> $locations */
        $locations = $query->get()->values();

        return new Response($locations);
    }

    public function search(SearchLocationsRequest $request): Response {
        $q = strtolower($request->query("q", ""));

        // We allow search with empty string only when an actor is provided.
        if (($q === '') && !$request->has("actor")) {
            return new Response([]);
        }

        $locations = Location::query()
                             ->with("network")
                             ->when($request->has("network"), function (Builder $query) use ($request) {
                                 $query->where("network_id", "=", $request->input("network"));
                             })
                             ->when($request->has("format"), function (Builder $query) use ($request) {
                                 $query->whereHas("display_type.formats", function (Builder $query) use ($request) {
                                     $query->where("id", "=", $request->input("format"));
                                 });
                             })
                             ->when($request->has("actor"), function (Builder $query) use ($request) {
                                 $query->whereHas("actors", function (Builder $query) use ($request) {
                                     $query->where("id", "=", $request->input("actor"));
                                 });
                             })
                             ->where('locations.name', 'LIKE', "%$q%")
                             ->get();

        return new Response($locations);
    }

    /**
     * @param ShowLocationRequest $request
     * @param Location            $location
     * @return Response
     */
    public function show(ShowLocationRequest $request, Location $location): Response {
        return new Response($location->withPublicRelations($request->input("with", [])));
    }

    /**
     * @param UpdateLocationRequest $request
     * @param Location              $location
     * @return Response
     * @throws UnknownProperties
     * @throws InvalidBroadcasterAdapterException
     */
    public function update(UpdateLocationRequest $request, Location $location): Response {
        $location->name = $request->input('name');

        $location->scheduled_sleep = $request->input("scheduled_sleep", false);
        $location->sleep_end       = $request->input("sleep_end");
        $location->sleep_start     = $request->input("sleep_start");
        $location->save();

        // If the broadcaster support
        /** @var BroadcasterOperator&BroadcasterLocationsSleepSimple $broadcaster */
        $broadcaster = BroadcasterAdapterFactory::makeForNetwork($location->network_id);

        if ($broadcaster->hasCapability(BroadcasterCapability::LocationsSleepSimple)) {
            $broadcaster->updateSleepSchedule($location->toExternalBroadcastIdResource(), $location->scheduled_sleep, $location->sleep_start->toTimeString(), $location->sleep_end->toTimeString());
        }

        return new Response($location->load('display_type'));
    }
}
