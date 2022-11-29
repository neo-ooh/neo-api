<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastResourcesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\BroadcastResources\ListResourceJobsRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastResources\ListResourcePerformancesRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastResources\ListResourceRepresentationsRequest;
use Neo\Modules\Broadcast\Http\Requests\BroadcastResources\ShowBroadcastResourceRequest;
use Neo\Modules\Broadcast\Models\BroadcastResource;

class BroadcastResourcesController extends Controller {
    public function show(ShowBroadcastResourceRequest $request, BroadcastResource $broadcastResource): Response {
        return new Response($broadcastResource->loadPublicRelations());
    }

    public function representations(ListResourceRepresentationsRequest $request, BroadcastResource $broadcastResource): Response {
        return new Response($broadcastResource->external_representations()->get());
    }

    public function jobs(ListResourceJobsRequest $request, BroadcastResource $broadcastResource): Response {
        return new Response($broadcastResource->jobs()->get());
    }

    public function performances(ListResourcePerformancesRequest $request, BroadcastResource $broadcastResource): Response {
        return new Response($broadcastResource->performances()->get());
    }
}
