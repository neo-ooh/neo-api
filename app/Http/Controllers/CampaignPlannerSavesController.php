<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignPlannerSavesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\CampaignPlannerSaves\DestroySaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\ListSavesRequest;
use Neo\Http\Requests\CampaignPlannerSaves\ShareSaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\StoreSaveRequest;
use Neo\Http\Requests\CampaignPlannerSaves\UpdateSaveRequest;
use Neo\Http\Resources\CampaignPlannerSaveResource;
use Neo\Models\Actor;
use Neo\Models\CampaignPlannerSave;

class CampaignPlannerSavesController {
    public function index(ListSavesRequest $request, Actor $actor) {
        return new Response($actor->campaign_planner_saves()->get());
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
        return new Response($actor->campaign_planner_saves()
                                  ->orderBy("updated_at", "desc")
                                  ->limit(5)
                                  ->get());
    }

    public function show(Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        $save = CampaignPlannerSave::query()
                                   ->from($campaignPlannerSave->getWriteTable())
                                   ->where("id", "=", $campaignPlannerSave->getKey())
                                   ->first();
        return new Response(new CampaignPlannerSaveResource($save->makeVisible("data")));
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
}
