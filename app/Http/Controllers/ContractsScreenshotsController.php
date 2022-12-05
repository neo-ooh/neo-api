<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractsScreenshotsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Screenshots\DestroyContractScreenshotsRequest;
use Neo\Http\Requests\Screenshots\DestroyScreenshotsRequest;
use Neo\Http\Requests\Screenshots\UpdateScreenshotsRequest;
use Neo\Jobs\Contracts\DeleteBurstJob;
use Neo\Models\Contract;
use Neo\Models\ContractScreenshot;

class ContractsScreenshotsController extends Controller {
    public function update(UpdateScreenshotsRequest $request, ContractScreenshot $screenshot) {
        $screenshot->is_locked = $request->input("is_locked");
        $screenshot->save();

        return new Response($screenshot);
    }

    public function destroy(DestroyScreenshotsRequest $request, ContractScreenshot $screenshot) {
        $screenshot->delete();

        return new Response([]);
    }

    public function destroyContractScreenshots(DestroyContractScreenshotsRequest $request, Contract $contract) {
        $deleteLocked = $request->input("delete_locked", false);

        foreach ($contract->bursts as $burst) {
            // If we want to keep locked screenshot, delete them one by one and check their status every time
            if (!$deleteLocked) {
                DeleteBurstJob::dispatch($burst->getKey(), true);
                continue;
            }

            $burst->delete();
        }

        return new Response(["status" => "ok"]);
    }
}
