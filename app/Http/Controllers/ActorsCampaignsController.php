<?php

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Neo\Models\Actor;

class ActorsCampaignsController extends Controller {
    public function index(Request $request, Actor $actor): Response {
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
                                  ->append(["related_campaigns", "related_libraries"]));
    }
}
