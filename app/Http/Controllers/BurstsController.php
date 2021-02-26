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
use Neo\Http\Requests\Bursts\StoreBurstRequest;
use Neo\Models\Burst;
use Neo\Models\Screenshot;

class BurstsController extends Controller {
    public function store(StoreBurstRequest $request): Response {
        [
            "locations"    => $locations,
            "report_id"    => $reportId,
            "start_at"     => $startAt,
            "scale_factor" => $scaleFactor,
            "duration_ms"  => $duration,
            "frequency_ms" => $frequency,
        ] = $request->validated();

        $bursts = [];

        foreach ($locations as $locationId) {
            $burst               = new Burst();
            $burst->report_id    = $reportId;
            $burst->location_id  = $locationId;
            $burst->requested_by = Auth::id();
            $burst->start_at     = $startAt;
            $burst->started      = false;
            $burst->scale_factor = $scaleFactor;
            $burst->duration_ms  = $duration;
            $burst->frequency_ms = $frequency;
            $burst->save();

            $bursts[] = $burst;
        }

        $burst->refresh();

        // And return the burst
        return new Response($bursts, 201);
    }

    public function receive(Request $request, Burst $burst): void {
        $screenshot           = new Screenshot();
        $screenshot->burst_id = $burst->id;
        $screenshot->save();

        $screenshot->store($request->getContent(true));

        // Check if the burst is complete
        if($burst->screenshots_count === $burst->expected_screenshots) {
            $burst->is_finished = true;
            $burst->save();
        }
    }

    public function show(Burst $burst): Response {
        return new Response($burst->load('screenshots', 'player', 'player.location'));
    }

    public function destroy(Burst $burst): Response {
        if(!$burst->started) {
            $burst->delete();
            return new Response();
        }

        return new Response(["Burst is already started"], 400);
    }
}
