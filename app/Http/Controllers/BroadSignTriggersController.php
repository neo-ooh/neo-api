<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignTriggersController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\BroadSignTriggers\ListBroadSignTriggersRequest;
use Neo\Http\Requests\BroadSignTriggers\ShowBroadSignTriggersRequest;
use Neo\Http\Requests\BroadSignTriggers\StoreBroadSignTriggersRequest;
use Neo\Http\Requests\BroadSignTriggers\UpdateBroadSignTriggersRequest;
use Neo\Models\BroadSignTrigger;

class BroadSignTriggersController extends Controller {
    public function index(ListBroadSignTriggersRequest $request) {
        return new Response(BroadSignTrigger::query()->orderBy("name")->get()->values());
    }

    public function show(ShowBroadSignTriggersRequest $request, BroadSignTrigger $trigger) {
        return new Response($trigger);
    }

    public function store(StoreBroadSignTriggersRequest $request) {
        $trigger = new BroadSignTrigger();
        [
            "name"                 => $trigger->name,
            "broadsign_trigger_id" => $trigger->broadsign_trigger_id,
        ] = $request->validated();
        $trigger->save();

        return new Response($trigger, 201);
    }

    public function update(UpdateBroadSignTriggersRequest $request, BroadSignTrigger $trigger) {
        [
            "name"                 => $trigger->name,
            "broadsign_trigger_id" => $trigger->broadsign_trigger_id,
        ] = $request->validated();
        $trigger->save();

        return new Response($trigger, 200);
    }

    public function destroy(BroadSignTrigger $trigger) {
        // Todo: Wait for trigger implementation in Campaigns for this method
    }
}
