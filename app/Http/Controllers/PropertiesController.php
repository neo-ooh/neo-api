<?php

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Neo\Enums\Capability;
use Neo\Http\Requests\Properties\DestroyPropertyRequest;
use Neo\Http\Requests\Properties\ListPropertiesRequest;
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
use Neo\Models\Location;
use Neo\Models\Property;
use Neo\Models\Province;

class PropertiesController extends Controller {
    public function index(ListPropertiesRequest $request) {
        $properties = Property::all();
        $properties->load([
            "data",
            "address",
            "actor" => fn($q) => $q->select(["id", "name"]),
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
            $properties->loadMissing(["traffic", "traffic.weekly_data"]);

            $properties->each(function ($p) {
                $p->rolling_weekly_traffic = $p->traffic->getRollingWeeklyTraffic();
            });

            $properties->makeHidden(["weekly_data", "weekly_traffic"]);
        }

        if (in_array("products", $request->input("with", []), true)) {
            $properties->loadMissing(["products"]);

            if (in_array("impressions_models", $request->input("with", []), true)) {
                $properties->loadMissing(["products.impressions_models"]);
            }
        }

        if (in_array("pictures", $request->input("with", []), true)) {
            $properties->load("pictures");
        }

        if (in_array("fields", $request->input("with", []), true)) {
            $properties->load([
                "network.properties_fields",
                "fields_values" => fn($q) => $q->select(["property_id", "fields_segment_id", "value"])
            ]);
        }

        return $properties;
    }

    public function store(StorePropertyRequest $request) {
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
        $property           = new Property();
        $property->actor_id = $actorId;
        $property->save();
        $property->refresh();

        // Try to identify the network from the property's actor locations
        $property->network_id = Location::query()->whereHas("actor", function ($query) use ($property) {
            $query->where("id", "=", $property->actor_id);
        })
                                        ->get("network_id")
                                        ->pluck("network_id")
                                        ->first();

        $property->save();

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

    public function show(ShowPropertyRequest $request, int $propertyId) {
        // Is this group a property ?
        /** @var Property $property */
        $property  = Property::query()->find($propertyId);
        $relations = $request->input("with", []);

        if ($property) {
            $property->load(["actor", "traffic", "traffic.data", "address"]);

            if (Gate::allows(Capability::properties_edit)) {
                $property->loadMissing(["data", "network", "network.properties_fields", "pictures", "fields_values", "traffic.source", "opening_hours"]);
            }

            if (in_array("products", $relations, true)) {
                $property->loadMissing(["products",
                                        "products.impressions_models",
                                        "products.locations",
                                        "products_categories",
                                        "products_categories.product_type"]);
            }

            if (Gate::allows(Capability::odoo_properties)) {
                $property->loadMissing(["odoo"]);
            }

            return new Response($property);
        }


        // Since the requested actor is not a property, we'll either load all its children data in a compound, or its own.
        /** @var Actor $actor */
        $actor = Actor::query()->find($propertyId);

        if ($request->input("summed", false)) {
            $actor->append("compound_traffic");
            $actor->makeHidden("property");
            return new Response($actor);
        }

        // Is there any children bellow ?
        /** @var Collection $childGroups */
        $childGroups = $actor->selectActors()
                             ->directChildren()
                             ->where("is_group", "=", true)
                             ->orderBy("name")
                             ->get();

        if ($childGroups->isEmpty()) {
            // No group children, and not a property, return 404;
            return new Response(null, 404);
        }

        $actor->properties = $childGroups->append("compound_traffic");

        $actor->properties->makeHidden("property");

        return new Response($actor);
    }

    public function update(UpdatePropertyRequest $request, Property $property) {
        $property->network_id = $request->input("network_id");
        $property->save();

        return new Response($property);
    }

    public function updateAddress(UpdateAddressRequest $request, Property $property) {
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

    public function destroy(DestroyPropertyRequest $request, Property $property) {
        $address = $property->address;
        $property->pictures->each(fn($picture) => $picture->delete());

        $property->traffic()->delete();
        $property->data()->delete();
        $property->odoo()->delete();
        $property->delete();
        $address?->delete();

        return new Response(["status" => "ok"]);
    }
}
