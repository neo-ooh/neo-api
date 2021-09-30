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
use Neo\Models\CampaignPlannerSave;

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
}
