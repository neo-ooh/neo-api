<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerSavesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\CampaignPlannerSaves\DestroySaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\ListSavesRequest;
use Neo\Http\Requests\CampaignPlannerSaves\ShareSaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\StoreSaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\UpdateSaveRequest;
use Neo\Http\Resources\CampaignPlannerSaveExcerptResource;
use Neo\Http\Resources\CampaignPlannerSaveResource;
use Neo\Models\Actor;
use Neo\Models\CampaignPlannerSave;
use Neo\Modules\Broadcast\Models\Network;
use Neo\Modules\Properties\Models\Brand;
use Neo\Modules\Properties\Models\ProductCategory;
use Neo\Modules\Properties\Models\Property;

class CampaignPlannerSavesController {
    public function index(ListSavesRequest $request, Actor $actor) {
        return new Response(CampaignPlannerSaveExcerptResource::collection($actor->campaign_planner_saves()->get()));
    }

    public function store(StoreSaveRequest $request) {
        $save = new CampaignPlannerSave([
                                            "actor_id" => Auth::user()->id,
                                            "name"     => $request->input("_meta")["name"],
                                            "data"     => [
                                                "plan"  => $request->input("plan"),
                                                "_meta" => $request->input("_meta"),
                                            ],
                                        ]);

        $save->save();


        return new Response(new CampaignPlannerSaveResource($save), 201);
    }

    public function recent(ListSavesRequest $request, Actor $actor) {
        return new Response(CampaignPlannerSaveResource::collection($actor->campaign_planner_saves()
                                                                          ->orderBy("updated_at", "desc")
                                                                          ->limit(5)
                                                                          ->get()));
    }

    public function show(Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        return new Response(new CampaignPlannerSaveResource($campaignPlannerSave));
    }

    public function update(UpdateSaveRequest $request, Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        $campaignPlannerSave->name = $request->input("_meta")["name"];
        $campaignPlannerSave->data = [
            "plan"  => $request->input("plan"),
            "_meta" => $request->input("_meta"),
        ];
        $campaignPlannerSave->save();

        return new Response(new CampaignPlannerSaveResource($campaignPlannerSave));
    }

    public function share(ShareSaveRequest $request, Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        $receivers = $request->input("actors");

        foreach ($receivers as $receiverId) {
            $newSave           = $campaignPlannerSave->replicate();
            $newSave->actor_id = $receiverId;
            $newSave->save();
        }

        return new Response([]);
    }

    public function destroy(DestroySaveRequest $request, Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        $campaignPlannerSave->delete();

        return new Response();
    }

    public function showWithData(Request $request, CampaignPlannerSave $campaignPlannerSave) {
        // We return the save and the data needed by the save in one go.
        // Extract properties IDs from the save
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

        return new Response([
                                "save"       => new CampaignPlannerSaveResource($campaignPlannerSave),
                                "properties" => $properties,
                                "categories" => $categories,
                                "networks"   => $networks,
                                "brands"     => $brands,
                            ]);
    }
}
