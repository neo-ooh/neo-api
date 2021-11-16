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
use Illuminate\Support\Facades\Log;
use Neo\Http\Requests\CampaignPlannerSaves\DestroySaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\ListSavesRequest;
use Neo\Http\Requests\CampaignPlannerSaves\StoreSaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\UpdateSaveRequest;
use Neo\Models\Actor;
use Neo\Models\CampaignPlannerSave;
use Neo\Models\Property;

class CampaignPlannerSavesController {
    public function index(ListSavesRequest $request, Actor $actor) {
        return new Response($actor->campaign_planner_saves()->get(["id", "name", "created_at", "updated_at"]));
    }

    public function store(StoreSaveRequest $request, Actor $actor) {
        $save = new CampaignPlannerSave([
            "actor_id" => $actor->id,
            "name"     => $request->input("name"),
            "data"     => $request->input("data"),
        ]);

        $save->save();

        return new Response($save, 201);
    }

    public function show(Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        return new Response($campaignPlannerSave);
    }

    public function update(UpdateSaveRequest $request, Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        $campaignPlannerSave->name = $request->input("name");
        $campaignPlannerSave->data = $request->input("data");
        $campaignPlannerSave->save();

        return new Response($campaignPlannerSave);
    }

    public function destroy(DestroySaveRequest $request, Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        $campaignPlannerSave->delete();

        return new Response();
    }

    public function showWithData(Request $request, CampaignPlannerSave $campaignPlannerSave) {
        // We return the save and the data needed by the save in one go.
        // Extract properties IDs from the save
        $propertiesIds = collect($campaignPlannerSave->data["flights"])->pluck("selection")->flatten(1)->pluck("0.0");
        $properties    = Property::query()->whereIn("actor_id", $propertiesIds)->get();

        $properties->load([
            "actor",
            "address",
            "data",
            "fields_values",
            "network",
            "network.properties_fields",
            "odoo",
            "odoo.products",
            "odoo.products_categories",
            "odoo.products_categories.product_type",
            "pictures",
            "traffic",
        ]);
        $properties->each(function (Property $p) {
            $p->traffic->loadMonthlyTraffic($p->address?->city->province);
            $p->odoo?->computeCategoriesValues();
        });

//        $campaignPlannerSave->load(["actor.name"]);

        Log::info("connect.log", [
            "action"   => "planner.static.load",
            "save_id"  => $campaignPlannerSave->id,
            "name"     => $campaignPlannerSave->name,
            "owner_id" => $campaignPlannerSave->actor_id,
            "contract" => $campaignPlannerSave->data["odoo"]["contract"] ?? "",
        ]);

        return new Response(["save" => $campaignPlannerSave, "properties" => $properties]);
    }
}
