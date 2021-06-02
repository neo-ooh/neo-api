<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ActorsCampaignsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ActorsCampaigns\ListActorCampaignsRequest;
use Neo\Models\Actor;

class ActorsCampaignsController extends Controller {
    public function index(ListActorCampaignsRequest $request): Response {

        $routeActor = $request->route('actor');

        if(is_object($routeActor)) {
            $actor = $routeActor;
        } else {
            $actor = Actor::query()->findOrFail($routeActor);
        }

        return new Response($actor->getCampaigns(true, true, false, false)
                                  ->loadMissing([
                                     "format",
                                     "locations",
                                     "owner",
                                     "shares",
                                     "schedules",
                                     "schedules.content",
                                     "schedules.owner:id,name",
                                     "trashedSchedules",
                                     "trashedSchedules.content"])
                                 ->append(["related_libraries"])->each(fn($campaign) => $campaign->owner->load("logo")->append("applied_branding")));
    }
}
