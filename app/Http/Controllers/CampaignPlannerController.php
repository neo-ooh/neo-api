<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Requests\CampaignPlanner\GetCampaignPlannerDataRequest;
use Neo\Http\Requests\CampaignPlanner\GetCampaignPlannerDemographicValuesRequest;
use Neo\Http\Requests\CampaignPlanner\GetCampaignPlannerTrafficRequest;
use Neo\Http\Resources\CampaignPlannerSaveResource;
use Neo\Models\CampaignPlannerSave;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Properties\Models\Brand;
use Neo\Modules\Properties\Models\DemographicValue;
use Neo\Modules\Properties\Models\DemographicVariable;
use Neo\Modules\Properties\Models\Field;
use Neo\Modules\Properties\Models\FieldsCategory;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Models\Property;
use Neo\Modules\Properties\Models\PropertyTrafficSnapshot;

class CampaignPlannerController {
    public function dataChunk_1(GetCampaignPlannerDataRequest $request) {
        /** @var Collection<Property> $properties */
        $properties = Property::query()->has("odoo")->get();

        $properties->load([
                              "actor.tags",
                              "data",
                              "address",
                              "odoo",
                              "pictures",
                              "tenants" => fn($q) => $q->select(["id"]),
                          ]);

        return [
            "properties" => $properties->map(fn(Property $property) => [
                "id"           => $property->actor_id,
                "name"         => $property->actor->name,
                "address"      => $property->address,
                "network_id"   => $property->network_id,
                "pricelist_id" => $property->pricelist_id,
                "data"         => $property->data,
                "pictures"     => $property->pictures,
                "has_tenants"  => $property->has_tenants,
                "tenants"      => $property->tenants->pluck('id'),
                "tags"         => $property->actor->tags,
            ]),
        ];
    }

    public function dataChunk_2(GetCampaignPlannerDataRequest $request) {
        /** @var Collection<Property> $properties */
        $properties = Property::query()->has("odoo")->get();

        $properties->load([
                              "fields_values" => fn($q) => $q->select(["property_id", "fields_segment_id", "value", "reference_value", "index"])
                                                             ->whereNull("reference_value"),
                          ]);

        return [
            "properties" => $properties->map(fn(Property $property) => [
                "id"            => $property->actor_id,
                "fields_values" => $property->fields_values,
            ]),
        ];
    }

    public function dataChunk_3(GetCampaignPlannerDataRequest $request) {
        /** @var Collection<Property> $properties */
        $properties = Property::query()->has("odoo")->get();

        $properties->load([
                              "products",
                              "products.attachments",
                              "products.impressions_models",
                          ]);

        return [
            "properties" => $properties->map(fn(Property $property) => [
                "id"                      => $property->actor_id,
                "products"                => $property->products,
                "products_ids"            => $property->products->pluck("id"),
                "products_categories_ids" => $property->products->groupBy("category_id")
                                                                ->map(static fn($products) => $products->pluck('id')),
            ]),
        ];
    }

    public function dataChunk_4(GetCampaignPlannerDataRequest $request) {
        $properties = Property::query()->has("odoo")->get(["actor_id", "pricelist_id"]);

        $categories           = ProductCategory::with(["impressions_models", "product_type", "attachments"])->get();
        $fieldCategories      = FieldsCategory::query()->get();
        $fields               = Field::query()
                                     ->with(["segments.stats"])
                                     ->get()
                                     ->append("network_ids");
        $demographicVariables = DemographicVariable::query()->get();
        $networks             = Network::query()->get();
        $brands               = Brand::query()->with("child_brands:id,parent_id")->get();
        $pricelists           = Pricelist::query()->whereIn("id", $properties->pluck("pricelist_id")->whereNotNull())
                                         ->with(["categories_pricings", "products_pricings"])
                                         ->get();

        return [
            "categories"            => $categories,
            "networks"              => $networks,
            "fields_categories"     => $fieldCategories,
            "fields"                => $fields,
            "demographic_variables" => $demographicVariables,
            "brands"                => $brands,
            "pricelists"            => $pricelists,
        ];
    }

    public function trafficChunk(GetCampaignPlannerTrafficRequest $request) {
        $date = $request->input("date");

        // If no date is provided, we use the most recent snapshot available
        if (!$date) {
            $date = DB::query()->select("date")
                      ->from((new PropertyTrafficSnapshot())->getTable())
                      ->orderBy("date", "desc")
                      ->first()->date;
        }

        $snapshots = PropertyTrafficSnapshot::query()->where("date", "=", $date)->get();

        return [
            "traffic" => $snapshots,
        ];
    }

    public function demographicValues(GetCampaignPlannerDemographicValuesRequest $request) {
        return new Response(DemographicValue::query()->whereIn("value_id", $request->input("variables"))->get());
    }

    public function save(Request $request, CampaignPlannerSave $campaignPlannerSave) {
        return new Response(new CampaignPlannerSaveResource($campaignPlannerSave));
    }
}
