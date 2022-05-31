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
use Neo\Http\Requests\CampaignPlanner\GetCampaignPlannerDataRequest;
use Neo\Http\Resources\CampaignPlannerSaveResource;
use Neo\Models\Brand;
use Neo\Models\CampaignPlannerSave;
use Neo\Models\FieldsCategory;
use Neo\Models\Network;
use Neo\Models\Pricelist;
use Neo\Models\ProductCategory;
use Neo\Models\Property;

class CampaignPlannerController {
    public function dataChunk_1(GetCampaignPlannerDataRequest $request) {
        /** @var Collection<Property> $properties */
        $properties = Property::query()->has("odoo")->get();

        $properties->load([
            "actor.tags",
            "data",
            "address",
            "odoo",
            "traffic",
            "traffic.weekly_data",
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
                "traffic"      => $property->traffic->getRollingWeeklyTraffic($property->network_id),
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
            "fields_values" => fn($q) => $q->select(["property_id", "fields_segment_id", "value", "reference_value", "index"]),
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

        $categories      = ProductCategory::with(["impressions_models", "product_type", "attachments"])->get();
        $fieldCategories = FieldsCategory::query()->get();
        $networks        = Network::query()->with(["properties_fields"])->get();
        $brands          = Brand::query()->with("child_brands:id,parent_id")->get();
        $pricelists      = Pricelist::query()->whereIn("id", $properties->pluck("pricelist_id")->whereNotNull())
                                    ->with("pricings")
                                    ->get();

        return [
            "categories"        => $categories,
            "networks"          => $networks,
            "fields_categories" => $fieldCategories,
            "brands"            => $brands,
            "pricelists"        => $pricelists,
        ];
    }

    public function save(Request $request, CampaignPlannerSave $campaignPlannerSave) {
        return new Response(new CampaignPlannerSaveResource($campaignPlannerSave));
    }
}
