<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractsScreenshotsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Requests\ContractsScreenshots\AssociateScreenshotRequest;
use Neo\Http\Requests\ContractsScreenshots\DissociateScreenshotRequest;
use Neo\Models\Contract;
use Neo\Models\Screenshot;

class ContractsScreenshotsController extends Controller {
    public function associate(AssociateScreenshotRequest $request, Contract $contract, Screenshot $screenshot) {
        $contract->screenshots()->attach($screenshot, ["flight_id" => $request->input("flight_id")]);

        return new Response($contract->screenshots);
    }

    public function dissociate(DissociateScreenshotRequest $request, Contract $contract, Screenshot $screenshot) {
        DB::table("contracts_screenshots")
          ->where("contract_id", "=", $contract->getKey())
          ->where("screenshot_id", "=", $screenshot->getKey())
          ->where(function (Builder $query) use ($request) {
              $query->where("flight_id", "=", $request->input("flight_id"))
                    ->orWhereNull("flight_id");
          })
          ->delete();

        return new Response($contract->screenshots);
    }
}
