<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficSnapshotsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\TrafficSnapshots\RefreshSnapshotRequest;
use Neo\Jobs\Properties\CreateTrafficSnapshotJob;

class TrafficSnapshotsController {
    public function refresh(RefreshSnapshotRequest $request) {
        CreateTrafficSnapshotJob::dispatchSync();

        return new Response(["status" => "ok"], 200);
    }
}
