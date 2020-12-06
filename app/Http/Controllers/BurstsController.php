<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - BurstsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Neo\BroadSign\Jobs\RequestScreenshotsBurst;
use Neo\Http\Requests\Bursts\StoreBurstRequest;
use Neo\Models\Burst;
use Neo\Models\Screenshot;

class BurstsController extends Controller {
    public function store(StoreBurstRequest $request): Response {
        // Create a new Burst
        $burst               = new Burst();
        $burst->requested_by = Auth::id();
        $burst->is_manual    = true;
        $burst->status       = "pending";

        [
            "player_id"    => $burst->player_id,
            "scale_factor" => $burst->scale_factor,
            "duration_ms"  => $burst->duration_ms,
            "frequency_ms" => $burst->frequency_ms,
            "start_at"     => $burst->started_at
        ] = $request->validated();
        $burst->save();

        // If the burst is set to start soon, create its job now
        if ($burst->started_at->diff(Date::now())->i < 1) {
            RequestScreenshotsBurst::dispatchAfterResponse($burst->id);
        }

        // And return the burst
        return new Response($burst, 201);
    }

    public function receive(Request $request, Burst $burst): void {
        $screenshot           = new Screenshot();
        $screenshot->burst_id = $burst->id;
        $screenshot->save();

        $screenshot->store($request->getContent(true));
    }

    public function show(Burst $burst): Response {
        return new Response($burst->load('screenshots', 'player', 'player.location'));
    }
}
