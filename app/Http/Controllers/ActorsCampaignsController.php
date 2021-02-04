<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ActorsCampaigns\ListActorCampaignsRequest;

class ActorsCampaignsController extends Controller {
    public function index(ListActorCampaignsRequest $request): Response {
        return new Response($request->route('actor')
                                    ->getCampaigns(true, true, false, false)
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
                                    ->append(["related_libraries"]));
    }
}
