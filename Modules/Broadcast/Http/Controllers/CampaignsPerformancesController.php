<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignsPerformancesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\CampaignsPerformances\ListCampaignPerformancesRequest;
use Neo\Modules\Broadcast\Models\Campaign;

class CampaignsPerformancesController extends Controller {
    public function index(ListCampaignPerformancesRequest $request, Campaign $campaign) {
        return new Response($campaign->performances()->get());
    }
}
