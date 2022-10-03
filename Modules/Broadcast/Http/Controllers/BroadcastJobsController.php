<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastJobsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\BroadcastJobs\RetryJobRequest;
use Neo\Modules\Broadcast\Models\BroadcastJob;

class BroadcastJobsController extends Controller {
    public function retry(RetryJobRequest $request, BroadcastJob $broadcastJob) {
        $broadcastJob->retry();

        return new Response(["status" => "ok"]);
    }
}
