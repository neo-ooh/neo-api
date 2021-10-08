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

use Illuminate\Http\Response;
use Neo\Http\Requests\CampaignPlannerSaves\DestroySaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\ListSavesRequest;
use Neo\Http\Requests\CampaignPlannerSaves\StoreSaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\UpdateSaveRequest;
use Neo\Models\Actor;
use Neo\Models\CampaignPlannerPolygon;
use Neo\Models\CampaignPlannerSave;

class CampaignPlannerPolygonsController {
    public function index(ListSavesRequest $request, Actor $actor) {
        return new Response($actor->campaign_planner_polygons);
    }

    public function store(StoreSaveRequest $request, Actor $actor) {
        $save = new CampaignPlannerPolygon([
            "actor_id" => $actor->id,
            "name"     => $request->input("name"),
            "data"     => $request->input("data"),
        ]);

        $save->save();

        return new Response($save, 201);
    }

    public function show(Actor $actor, CampaignPlannerPolygon $campaignPlannerPolygon) {
        return new Response($campaignPlannerPolygon);
    }

    public function update(UpdateSaveRequest $request, Actor $actor, CampaignPlannerPolygon $campaignPlannerPolygon) {
        $campaignPlannerPolygon->name = $request->input("name");
        $campaignPlannerPolygon->data = $request->input("data");
        $campaignPlannerPolygon->save();

        return new Response($campaignPlannerPolygon);
    }

    public function destroy(DestroySaveRequest $request, Actor $actor, CampaignPlannerPolygon $campaignPlannerPolygon) {
        $campaignPlannerPolygon->delete();

        return new Response();
    }
}
