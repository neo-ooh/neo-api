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
use Neo\Http\Requests\Screenshots\DestroyScreenshotsRequest;
use Neo\Models\ContractScreenshot;

class ContractsScreenshotsController extends Controller {
    public function destroy(DestroyScreenshotsRequest $request, ContractScreenshot $screenshot) {
        $screenshot->delete();

        return new Response([]);
    }
}
