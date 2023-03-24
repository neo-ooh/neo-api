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
use Neo\Documents\ProgrammaticExport\ProgrammaticExport;
use Neo\Enums\Capability;
use Neo\Http\Controllers\Controller;
use Neo\Jobs\Properties\PullOpeningHoursJob;
use Neo\Jobs\PullAddressGeolocationJob;
use Neo\Models\Actor;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Province;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Properties\Enums\TrafficFormat;
use Neo\Modules\Properties\Http\Requests\Properties\DestroyPropertyRequest;
use Neo\Modules\Properties\Http\Requests\Properties\DumpPropertyRequest;
use Neo\Modules\Properties\Http\Requests\Properties\ListPropertiesByIdRequest;
use Neo\Modules\Properties\Http\Requests\Properties\ListPropertiesPendingReviewRequest;
use Neo\Modules\Properties\Http\Requests\Properties\ListPropertiesRequest;
use Neo\Modules\Properties\Http\Requests\Properties\MarkPropertyReviewedRequest;
use Neo\Modules\Properties\Http\Requests\Properties\ShowPropertyRequest;
use Neo\Modules\Properties\Http\Requests\Properties\StorePropertyRequest;
use Neo\Modules\Properties\Http\Requests\Properties\UpdateAddressRequest;
use Neo\Modules\Properties\Http\Requests\Properties\UpdatePropertyRequest;
use Neo\Modules\Properties\Models\Property;

class PropertiesController extends Controller {
    public function index(ListPropertiesRequest $request) {
        $query = Property::query();

        if ($request->has("network_id")) {
            $query->where("network_id", "=", $request->input("network_id"));
        }

        $properties = $query->get();

        $properties->load([
                              "address",
                          ]);

        $expansion = $request->input("with", []);

        if (in_array("fields", $request->input("with", []), true)) {
            $properties->load([
                                  "fields_values" => fn($q) => $q->select(["property_id", "fields_segment_id", "value"]),
                              ]);
        }

        if (in_array("tenants", $request->input("with", []), true)) {
            $properties->load([
                                  "tenants" => fn($q) => $q->select(["id"]),
                              ]);
        }

        $public = array_diff($expansion, ["weekly_traffic", "rolling_weekly_traffic", "fields", "tenants"]);

        return new Response($properties->sortBy("actor.name")->values()->loadPublicRelations($public));
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
        /** @var Property $property */
        $property = Property::query()->find($propertyId);

        $relations = $request->input("with", []);

        if (!Gate::allows(Capability::properties_edit->value)) {
            // Remove properties that cannot be accessed without the capability
            $relations = array_diff($relations, [
                "network",
                "network.properties_fields",
                "pictures",
                "fields",
                "opening_hours",
                "warnings",
            ]);
        }

        if (in_array("products", $relations, true)) {
            $property->loadMissing(["products",
                                    "products.impressions_models",
                                    "products.locations",
                                    "products.attachments",
                                    "products.loop_configurations",
                                    "products_categories",
                                    "products_categories.attachments",
                                    "products_categories"]);
        }

        return new Response($property->loadPublicRelations());
    }

    public function update(UpdatePropertyRequest $request, Property $property): Response {
        $property->network_id   = $request->input("network_id");
        $property->is_sellable  = $request->input("is_sellable");
        $property->has_tenants  = $request->input("has_tenants");
        $property->pricelist_id = $request->input("pricelist_id", null);
        $property->save();

        return new Response($property->loadPublicRelations());
    }

    public function updateAddress(UpdateAddressRequest $request, Property $property): Response {
        /** @var Province $province */
        $province = Province::query()
                            ->where("slug", "=", $request->input("province"))
                            ->first();

        /** @var City $city */
        $city = City::query()->firstOrCreate([
                                                 "name"        => $request->input("city"),
                                                 "province_id" => $province->id,
                                             ]);

        $address          = $property->address ?? new Address();
        $address->line_1  = $request->input("line_1");
        $address->line_2  = $request->input("line_2");
        $address->city_id = $city->id;
        $address->zipcode = $request->input("zipcode");
        $address->save();

        $property->address()->associate($address);
        $property->save();

        PullAddressGeolocationJob::dispatch($address);

        return new Response($address);
    }

    public function destroy(DestroyPropertyRequest $request, Property $property): Response {
        $property->delete();

        return new Response(["status" => "ok"]);
    }

    /**
     * @param DumpPropertyRequest $request
     * @param Property            $property
     * @throws UnknownGenerationException
     */
    public function dump(DumpPropertyRequest $request, Property $property): void {
        $doc = ProgrammaticExport::make([$property->getKey()]);
        $doc->build();
        $doc->output();
    }

    /**
     * @param DumpPropertyRequest $request
     * @param Network             $network
     * @throws UnknownGenerationException
     */
    public function dumpNetwork(DumpPropertyRequest $request, Network $network) {
        set_time_limit(90);
        $doc = ProgrammaticExport::make(Property::query()
                                                ->where("network_id", "=", $network->getKey())
                                                ->setEagerLoads([])
                                                ->get()
                                                ->pluck("actor_id")
                                                ->toArray());
        $doc->build();
        $doc->output();
    }
}
