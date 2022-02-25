<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractBurstsController.php
 */

namespace Neo\Http\Controllers;

use Error;
use Exception;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Http\Requests\Bursts\StoreBurstRequest;
use Neo\Models\ContractBurst;
use Neo\Models\ContractScreenshot;

class ContractBurstsController extends Controller {
    public function store(StoreBurstRequest $request): Response {
        [
            "locations"     => $locations,
            "contract_id"   => $reportId,
            "start_at"      => $startAt,
            "scale_percent" => $scalePercent,
            "duration_ms"   => $duration,
            "frequency_ms"  => $frequency,
        ] = $request->validated();

        // If the user is not allowed to select the burst quality, we make it is set to the default value
        if (!Gate::allows(Capability::bursts_quality)) {
            $scalePercent = config("modules.broadsign.bursts.default-quality");
        }

        $bursts = [];

        foreach ($locations as $locationId) {
            $burst                = new ContractBurst();
            $burst->contract_id   = $reportId;
            $burst->location_id   = $locationId;
            $burst->actor_id      = Auth::id();
            $burst->start_at      = $startAt;
            $burst->status        = "PENDING";
            $burst->scale_percent = $scalePercent;
            $burst->duration_ms   = $duration;
            $burst->frequency_ms  = $frequency;
            $burst->save();
            $burst->refresh();

            $bursts[] = $burst->load("location");
        }

        // And return the burst
        return new Response($bursts, 201);
    }

    /**
     * @throws Exception
     */
    public function receive(Request $request, ContractBurst $burst): void {
        $screenshot           = new ContractScreenshot();
        $screenshot->burst_id = $burst->id;
        $screenshot->save();

        $tries     = 0;
        $succeeded = false;
        do {
            try {
                $screenshot->store($request->getContent(true));
                $succeeded = true;
            } catch (ServerException $e) {
                if ($e->getCode() === 503) {
                    $tries++;
                    usleep(random_int(0, 1_000_000));
                }
            }
        } while ($tries < 5 && !$succeeded);

        if (!$succeeded) {
            throw new Error("Could not reliably communicate with CDN.");
        }

        // Check if the burst is complete
        if ($burst->screenshots_count >= $burst->expected_screenshots - 1) {
            $burst->status = "OK";
            $burst->save();
        }
    }

    public function show(ContractBurst $burst): Response {
        return new Response($burst->load('screenshots', 'location'));
    }

    public function destroy(ContractBurst $burst): Response {
        if ($burst->status !== "OK") {
            $burst->delete();
            return new Response();
        }

        return new Response(["Burst is already started"], 400);
    }
}
