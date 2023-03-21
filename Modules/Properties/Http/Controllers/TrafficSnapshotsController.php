<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TrafficSnapshotsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Http\Requests\TrafficSnapshots\RefreshSnapshotRequest;
use Neo\Modules\Properties\Jobs\CreateTrafficSnapshotJob;

class TrafficSnapshotsController {
    public function refresh(RefreshSnapshotRequest $request) {
        CreateTrafficSnapshotJob::dispatchSync();

        return new Response(["status" => "ok"], 200);
    }
}
