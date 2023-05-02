<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Neo\Documents\Exceptions\UnknownGenerationException;
use Neo\Documents\PropertiesExport\PropertiesExport;
use Neo\Enums\Capability;
use Neo\Http\Controllers\Controller;
use Neo\Jobs\Properties\PullOpeningHoursJob;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Properties\Enums\TrafficFormat;
use Neo\Modules\Properties\Http\Requests\Properties\DestroyPropertyRequest;
use Neo\Modules\Properties\Http\Requests\Properties\DumpPropertyRequest;
use Neo\Modules\Properties\Http\Requests\Properties\ExportPropertiesRequest;
use Neo\Modules\Properties\Http\Requests\Properties\ListPropertiesByIdRequest;
use Neo\Modules\Properties\Http\Requests\Properties\ListPropertiesPendingReviewRequest;
use Neo\Modules\Properties\Http\Requests\Properties\ListPropertiesRequest;
use Neo\Modules\Properties\Http\Requests\Properties\MarkPropertyReviewedRequest;
use Neo\Modules\Properties\Http\Requests\Properties\ShowPropertyRequest;
use Neo\Modules\Properties\Http\Requests\Properties\StorePropertyRequest;
use Neo\Modules\Properties\Http\Requests\Properties\UpdatePropertyRequest;
use Neo\Modules\Properties\Models\Property;

class PropertiesController extends Controller {
    public function index(ListPropertiesRequest $request) {
        $query = Property::query();

        if ($request->has("network_id")) {
            $query->where("network_id", "=", $request->input("network_id"));
        }

        $properties = $query->get();

        return new Response($properties->sortBy("actor.name")->values()->loadPublicRelations());
    }

    public function byId(ListPropertiesByIdRequest $request) {
        return new Response(Property::query()->findMany($request->input("ids", []))->loadPublicRelations());
    }

    public function needAttention(ListPropertiesPendingReviewRequest $request) {
        /** @noinspection NullPointerExceptionInspection */
        $accessibleActors = Auth::user()->getAccessibleActors()->pluck("id");
        $properties       = Property::query()
                                    ->whereIn("actor_id", $accessibleActors)
                                    ->where("last_review_at", "<", Date::now()->startOf("month"))
                                    ->limit(26)
                                    ->orderBy("last_review_at")
                                    ->with(["traffic", "traffic.data", "tenants"])
                                    ->get();

        return new Response($properties);
    }

    public function markReviewed(MarkPropertyReviewedRequest $request, Property $property) {
        $property->last_review_at = Date::now();
        $property->save();

        new Response(["status" => "ok"]);
    }

    public function store(StorePropertyRequest $request): Response {
        // We need to make sure that the targeted actor is indeed a group
        $actorId = $request->input("actor_id");
        /** @var Actor $actor */
        $actor = Actor::find($actorId);

        if (!$actor->is_group) {
            throw new InvalidArgumentException("Only groups can be properties. $actor->name is not a group.");
        }

        // And that the group is not already a property
        if ($actor->is_property) {
            throw new InvalidArgumentException("This group is already a property");
        }

        // All good
        // Create the property
        $property                 = new Property();
        $property->actor_id       = $actorId;
        $property->network_id     = $request->input("network_id");
        $property->last_review_at = Date::now();
        $property->save();
        $property->refresh();

        // Create the traffic records for the property
        $property->traffic()->create([
                                         "format" => TrafficFormat::MonthlyMedian->value,
                                     ]);
        $property->translations()->createMany([
                                                  ["locale" => "fr-CA"],
                                                  ["locale" => "en-CA"],
                                              ]);

        PullOpeningHoursJob::dispatch($property->getKey());

        $property->load(["actor", "traffic", "traffic.data", "address"]);

        if (Gate::allows(Capability::properties_edit->value)) {
            $property->load(["opening_hours"]);
        }

        return new Response($property, 201);
    }

    public function show(ShowPropertyRequest $request, int $propertyId): Response {
        // Is this group a property ?
        /** @var Property|null $property */
        $property = Property::query()->find($propertyId);

        if (!$property) {
            return new Response([], 404);
        }

        return new Response($property->loadPublicRelations());
    }

    public function update(UpdatePropertyRequest $request, Property $property): Response {
        $property->network_id   = $request->input("network_id");
        $property->is_sellable  = $request->input("is_sellable");
        $property->has_tenants  = $request->input("has_tenants");
        $property->pricelist_id = $request->input("pricelist_id", null);
        $property->website      = $request->input("website") ?? "";
        $property->save();

        return new Response($property->loadPublicRelations());
    }

    public function destroy(DestroyPropertyRequest $request, Property $property): Response {
        $property->delete();

        return new Response(["status" => "ok"]);
    }

    /**
     * @throws UnknownGenerationException
     */
    public function export(ExportPropertiesRequest $request) {
        set_time_limit(120);
        $doc = PropertiesExport::make([
                                          "properties" => $request->input("ids"),
                                          "level"      => $request->input("level", null),
                                      ]);
        $doc->build();
        $doc->output();
    }

    /**
     * @param DumpPropertyRequest $request
     * @param Network             $network
     * @throws UnknownGenerationException
     */
    public function dumpNetwork(DumpPropertyRequest $request, Network $network) {
        set_time_limit(120);
        $doc = PropertiesExport::make([
                                          "properties" => Property::query()
                                                                  ->where("network_id", "=", $network->getKey())
                                                                  ->setEagerLoads([])
                                                                  ->get()
                                                                  ->pluck("actor_id")
                                                                  ->toArray(),
                                          "level"      => $request->input("level", null),
                                      ]);
        $doc->build();
        $doc->output();
    }
}
