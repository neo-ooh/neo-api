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

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JetBrains\PhpStorm\ArrayShape;
use Neo\Http\Requests\CampaignPlanner\GetCampaignPlannerDataRequest;
use Neo\Http\Resources\CampaignPlannerSaveResource;
use Neo\Models\Brand;
use Neo\Models\CampaignPlannerSave;
use Neo\Models\Network;
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
        "properties" => "\Illuminate\Database\Eloquent\Collection<Property>",
        "categories" => "\Illuminate\Database\Eloquent\Collection<ProductCategory>",
        "networks"   => "\Illuminate\Database\Eloquent\Collection<Network>",
        "brands"     => "\Illuminate\Database\Eloquent\Collection<Brand>"])
    ]
    protected function getCampaignPlannerData(): array {
        $properties = Property::query()->get();

        $properties->load([
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
            "fields_values" => fn($q) => $q->select(["property_id", "fields_segment_id", "value"]),
            "tenants"       => fn($q) => $q->select(["id"]),
        ]);


        $properties->each(function (Property $property) {
            $property->rolling_weekly_traffic = $property->traffic->getRollingWeeklyTraffic($property->network_id);
        });

        $properties->makeHidden(["weekly_data", "weekly_traffic"]);

        $categories = ProductCategory::with(["impressions_models", "product_type", "attachments"])->get();
        $networks   = Network::query()->with(["properties_fields"])->get();
        $brands     = Brand::query()->get();

        return [
            "properties" => $properties,
            "categories" => $categories,
            "networks"   => $networks,
            "brands"     => $brands,
        ];
    }
}
