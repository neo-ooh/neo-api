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
use Neo\Models\Actor;
use Neo\Models\CampaignPlannerSave;
use Neo\Resources\CampaignPlannerPlan\CampaignPlannerPlan;

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

		return new Response($query->get()->makeHidden("data"), 200, [
			"Content-Range" => "items $from-$to/$totalCount",
		]);
	}

	public function store(StoreSaveRequest $request) {
		$plan = CampaignPlannerPlan::from(json_decode($request->input("plan"), true));

		$save           = new CampaignPlannerSave();
		$save->actor_id = Auth::id();
		$save->name     = $request->input("name");
		$save->version  = $request->input("version");

		$save->contract        = $plan->plan->odoo?->contract;
		$save->client_name     = $plan->plan->odoo?->partnerName[1];
		$save->advertiser_name = $plan->plan->odoo?->analyticAccountName !== null ? $plan->plan->odoo?->analyticAccountName[1] : null;

		$save->save();

		$plan->_meta->id       = $save->id;
		$plan->_meta->uid      = $save->uid;
		$plan->_meta->actor_id = $save->actor_id;

		$save->storePlan(json_encode($plan, JSON_UNESCAPED_UNICODE));

		return new Response($save, 201);
	}

	public function show(Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
		return new Response($campaignPlannerSave);
	}

	public function update(UpdateSaveRequest $request, Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
		$campaignPlannerSave->name    = $request->input("name");
		$campaignPlannerSave->version = $request->input("version");

		$rawPlan = $request->input("plan");
		$plan    = CampaignPlannerPlan::from(json_decode($rawPlan, true));

		$campaignPlannerSave->contract        = $plan->plan->odoo?->contract;
		$campaignPlannerSave->client_name     = $plan->plan->odoo?->partnerName[1];
		$campaignPlannerSave->advertiser_name = $plan->plan->odoo?->analyticAccountName !== null ? $plan->plan->odoo?->analyticAccountName[1] : null;

		$campaignPlannerSave->save();

		$campaignPlannerSave->storePlan($rawPlan);

		return new Response($campaignPlannerSave);
	}

	public function share(ShareSaveRequest $request, Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
		$receivers = $request->input("actors");

		foreach ($receivers as $receiverId) {
			$newSave                  = new CampaignPlannerSave();
			$newSave->name            = $campaignPlannerSave->name;
			$newSave->version         = $campaignPlannerSave->version;
			$newSave->actor_id        = $receiverId;
			$newSave->contract        = $campaignPlannerSave->contract;
			$newSave->client_name     = $campaignPlannerSave->client_name;
			$newSave->advertiser_name = $campaignPlannerSave->advertiser_name;
			$newSave->save();

			clock()->event("Copy to Actor #$receiverId")->color("purple")->begin();
//			Storage::disk("public")->copy($campaignPlannerSave->plan_path, $newSave->plan_path);

			$plan                  = $campaignPlannerSave->getPlan();
			$plan->_meta->id       = $newSave->getKey();
			$plan->_meta->uid      = $newSave->uid;
			$plan->_meta->actor_id = $newSave->actor_id;

			$newSave->storePlan(json_encode($plan, JSON_UNESCAPED_UNICODE));

			clock()->event("Copy to Actor #$receiverId")->end();
		}


		return new Response([]);
	}

	public function destroy(DestroySaveRequest $request, Actor $actor, CampaignPlannerSave $campaignPlannerSave) {
		$campaignPlannerSave->delete();

		return new Response();
	}
}
