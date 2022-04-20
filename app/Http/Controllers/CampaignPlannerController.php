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
use JetBrains\PhpStorm\ArrayShape;
use Neo\Http\Requests\CampaignPlanner\GetCampaignPlannerDataRequest;
use Neo\Http\Resources\CampaignPlannerPropertyResource;
use Neo\Http\Resources\CampaignPlannerSaveResource;
use Neo\Models\Brand;
use Neo\Models\CampaignPlannerSave;
use Neo\Models\FieldsCategory;
use Neo\Models\Network;
use Neo\Models\Pricelist;
use Neo\Models\ProductCategory;
use Neo\Models\Property;

class CampaignPlannerController {
    public function data(GetCampaignPlannerDataRequest $request) {
        return new Response($this->getCampaignPlannerData());
    }

    public function saveAndDate(Request $request, CampaignPlannerSave $campaignPlannerSave) {
        return new Response(array_merge([
            "save" => new CampaignPlannerSaveResource($campaignPlannerSave)
        ],
            $this->getCampaignPlannerData(),
        ));
    }

    #[ArrayShape([
        "properties"        => "\Illuminate\Database\Eloquent\Collection<Property>",
        "categories"        => "\Illuminate\Database\Eloquent\Collection<ProductCategory>",
        "networks"          => "\Illuminate\Database\Eloquent\Collection<Network>",
        "brands"            => "\Illuminate\Database\Eloquent\Collection<Brand>",
        "fields_categories" => "\Illuminate\Database\Eloquent\Collection<FieldsCategory>",
        "pricelists"        => "\Illuminate\Database\Eloquent\Collection<Pricelist>"])
    ]
    protected function getCampaignPlannerData(): array {
        /** @var Collection<Property> $properties */
        $properties = Property::query()->get();

        $properties->load([
            "actor.tags",
            "data",
            "address",
            "odoo",
            "fields_values",
            "products",
            "products.attachments",
            "products.impressions_models",
            "traffic",
            "traffic.weekly_data",
            "pictures",
            "fields_values" => fn($q) => $q->select(["property_id", "fields_segment_id", "value", "reference_value", "index"]),
            "tenants"       => fn($q) => $q->select(["id"]),
        ]);


        $properties->each(function (Property $property) {
            $property->rolling_weekly_traffic = $property->traffic->getRollingWeeklyTraffic($property->network_id);
        });

        $properties->makeHidden(["weekly_data", "weekly_traffic"]);

        $categories      = ProductCategory::with(["impressions_models", "product_type", "attachments"])->get();
        $fieldCategories = FieldsCategory::query()->get();
        $networks        = Network::query()->with(["properties_fields"])->get();
        $brands          = Brand::query()->with("child_brands:id,parent_id")->get();

        $pricelists = Pricelist::query()->whereIn("id", $properties->pluck("pricelist_id")->whereNotNull())
                               ->with("pricings")
                               ->get();

        return [
            "properties"        => CampaignPlannerPropertyResource::collection($properties),
            "categories"        => $categories,
            "networks"          => $networks,
            "fields_categories" => $fieldCategories,
            "brands"            => $brands,
            "pricelists"        => $pricelists,
        ];
    }
}
