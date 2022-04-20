<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesController.php
 */

namespace Neo\Http\Controllers;

use Fuse\Fuse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Neo\Documents\PropertyDump\PropertyDump;
use Neo\Enums\Capability;
use Neo\Http\Requests\Properties\DestroyPropertyRequest;
use Neo\Http\Requests\Properties\DumpPropertyRequest;
use Neo\Http\Requests\Properties\ListPropertiesPendingReviewRequest;
use Neo\Http\Requests\Properties\ListPropertiesRequest;
use Neo\Http\Requests\Properties\MarkPropertyReviewedRequest;
use Neo\Http\Requests\Properties\SearchPropertiesRequest;
use Neo\Http\Requests\Properties\ShowPropertyRequest;
use Neo\Http\Requests\Properties\StorePropertyRequest;
use Neo\Http\Requests\Properties\UpdateAddressRequest;
use Neo\Http\Requests\Properties\UpdatePropertyRequest;
use Neo\Jobs\Odoo\PushPropertyGeolocationJob;
use Neo\Jobs\Properties\PullOpeningHoursJob;
use Neo\Jobs\PullAddressGeolocationJob;
use Neo\Jobs\PullPropertyAddressFromBroadSignJob;
use Neo\Models\Actor;
use Neo\Models\Address;
use Neo\Models\City;
use Neo\Models\Network;
use Neo\Models\Property;
use Neo\Models\Province;

class PropertiesController extends Controller {
    public function index(ListPropertiesRequest $request) {
        $properties = Property::all();
        $properties->load([
            "data",
            "address",
            "odoo",
        ]);

        if (in_array("network", $request->input("with", []), true)) {
            $properties->load("network");
        }

        if (in_array("traffic", $request->input("with", []), true)) {
            $properties->load(["traffic.data"]);
        }

        if (in_array("rolling_monthly_traffic", $request->input("with", []), true)) {
            $properties->loadMissing([
                "traffic",
                "traffic.data" => fn($q) => $q->select(["property_id", "year", "month", "final_traffic"])
            ]);

            $properties->each(function ($p) {
                $p->rolling_monthly_traffic = $p->traffic->getMonthlyTraffic($p->address?->city->province);
            });

            $properties->makeHidden("traffic");
        }

        if (in_array("weekly_traffic", $request->input("with", []), true)) {
            $properties->loadMissing([
                "traffic",
                "traffic.weekly_data"
            ]);

            $properties->each(function (Property $p) {
                $p->traffic->append("weekly_traffic");
                $p->traffic->makeHidden("weekly_data");
            });
        }

        if (in_array("rolling_weekly_traffic", $request->input("with", []), true)) {
            $properties->loadMissing(["traffic"]);

            clock()->event("Calculating rolling weekly traffic")->begin();
            $properties->each(function ($p) {
                $p->rolling_weekly_traffic = $p->traffic->getRollingWeeklyTraffic($p->network_id);
            });
            clock()->event("Calculating rolling weekly traffic")->end();

            $properties->makeHidden(["weekly_data", "weekly_traffic"]);
        }

        if (in_array("products", $request->input("with", []), true)) {
            $properties->loadMissing(["products",
                                      "products.attachments",
            ]);

            if (in_array("impressions_models", $request->input("with", []), true)) {
                $properties->loadMissing(["products.impressions_models"]);
            }
        }

        if (in_array("pictures", $request->input("with", []), true)) {
            $properties->load("pictures");
        }

        if (in_array("fields", $request->input("with", []), true)) {
            $properties->load([
                "fields_values" => fn($q) => $q->select(["property_id", "fields_segment_id", "value"])
            ]);
        }

        if (in_array("tenants", $request->input("with", []), true)) {
            $properties->load([
                "tenants" => fn($q) => $q->select(["id"])
            ]);
        }

        return $properties;
    }

    public function search(SearchPropertiesRequest $request) {
        /** @var Collection<Actor> $accessibleActors */
        $accessibleActors = Auth::user()->getAccessibleActors();
        $accessibleActors->load("parent");

        $searchEngine = new Fuse($accessibleActors->map(fn(Actor $actor) => [
            "id"          => $actor->getKey(),
            "name"        => $actor->name,
            "parent_name" => $actor->parent?->name,
        ])->toArray(), [
            "isCaseSensitive" => false,
            "includeScore"    => true,
            "keys"            => [
                "name",
                "parent_name",
            ],
        ]);

        $matchedIds = collect($searchEngine->search($request->input("q")))->pluck("item.id");
        $actorIds   = $accessibleActors->whereIn("id", $matchedIds)
                                       ->pluck("id")
                                       ->unique();

        $properties = Property::query()
                              ->whereIn("actor_id", $actorIds)
                              ->orderByRaw("FIELD(actor_id, {$matchedIds->join(',')})")
                              ->get();

        return new Response($properties);
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

        // Create the data and traffic records for the property
        $property->data()->create();
        $property->traffic()->create();

        // Load the address of the property
        PullPropertyAddressFromBroadSignJob::dispatch($property->getKey());
        PullOpeningHoursJob::dispatch($property->getKey());

        $property->load(["actor", "traffic", "traffic.data", "address"]);

        if (Gate::allows(Capability::properties_edit)) {
            $property->load(["data", "opening_hours"]);
        }

        if (Gate::allows(Capability::odoo_properties)) {
            $property->load(["odoo", "products", "products.product_type"]);
        }

        return new Response($property, 201);
    }

    public function show(ShowPropertyRequest $request, int $propertyId): Response {
        // Is this group a property ?
        /** @var Property $property */
        $property  = Property::query()->find($propertyId);
        $relations = $request->input("with", []);
        $property->load(["actor", "actor.tags", "traffic", "traffic.data", "address"]);

        if (Gate::allows(Capability::properties_edit)) {
            $property->loadMissing([
                "data",
                "network",
                "network.properties_fields",
                "pictures",
                "fields_values",
                "traffic.source",
                "opening_hours"
            ]);

            $property->append(["warnings"]);
        }

        if (in_array("products", $relations, true)) {
            $property->loadMissing(["products",
                                    "products.impressions_models",
                                    "products.locations",
                                    "products.attachments",
                                    "products_categories",
                                    "products_categories.attachments",
                                    "products_categories.product_type"]);
        }

        if (in_array("tenants", $relations, true)) {
            $property->loadMissing(["tenants"]);
        }

        if (Gate::allows(Capability::odoo_properties)) {
            $property->loadMissing(["odoo"]);
        }

        return new Response($property);
    }

    public function update(UpdatePropertyRequest $request, Property $property): Response {
        $property->network_id   = $request->input("network_id");
        $property->has_tenants  = $request->input("has_tenants");
        $property->pricelist_id = $request->input("pricelist_id", null);
        $property->save();

        return new Response($property);
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

        if ($property->odoo) {
            PushPropertyGeolocationJob::dispatch($property->id);
        }

        return new Response($address);
    }

    public function destroy(DestroyPropertyRequest $request, Property $property): Response {
        $address = $property->address;
        $property->pictures->each(fn($picture) => $picture->delete());

        $property->traffic()->delete();
        $property->data()->delete();
        $property->odoo()->delete();
        $property->delete();
        $address?->delete();

        return new Response(["status" => "ok"]);
    }

    public function dump(DumpPropertyRequest $request, Property $property): void {
        $doc = new PropertyDump([$property->getKey()]);
        $doc->build();
        $doc->output();
    }

    public function dumpNetwork(DumpPropertyRequest $request, Network $network) {
        set_time_limit(90);
        $doc = new PropertyDump(Property::query()
                                        ->where("network_id", "=", $network->getKey())
                                        ->setEagerLoads([])
                                        ->get()
                                        ->pluck("actor_id")
                                        ->toArray());

        $doc->build();
        $doc->output();

//        return new Response();
    }
}
