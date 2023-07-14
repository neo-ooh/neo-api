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
        $query = $actor->campaign_planner_saves();

        $totalCount = $query->clone()->count();
        $from       = 0;
        $to         = $totalCount;

        if ($request->has("page") || $request->has("count")) {
            $page  = $request->input("page", 1);
            $count = $request->input("count", 500);
            $from  = ($page - 1) * $count;
            $to    = ($page * $count) - 1;

            $query->limit($count)
                  ->offset($from);
        }

        $query->orderBy("updated_at", 'desc');

        return new Response($query->get(), 200, [
            "Content-Range" => "items $from-$to/$totalCount",
        ]);
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

    public function show(Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        $campaignPlannerSave->data = $campaignPlannerSave->getPlan();
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
            $newSave           = new CampaignPlannerSave();
            $newSave->name     = $campaignPlannerSave->name;
            $newSave->actor_id = $receiverId;
            $newSave->data     = $campaignPlannerSave->getPlan();
            $newSave->save();
        }

        return new Response([]);
    }

    public function destroy(DestroySaveRequest $request, Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
        $campaignPlannerSave->delete();

        return new Response();
    }
}
