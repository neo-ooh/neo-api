<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PlayRecordsController.php
 */

namespace Neo\Modules\Dynamics\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Dynamics\Http\Requests\PlayRecords\StoreRecordsRequest;
use Neo\Modules\Dynamics\Models\PlayRecord;
use Neo\Modules\Dynamics\Models\Structs\PlayRecordStruct;

class PlayRecordsController extends Controller {
	public function store(StoreRecordsRequest $request) {
		$playerId = $request->input("player_id");
		$records  = PlayRecordStruct::collection($request->input("records"));

		$now = Carbon::now()->setTimezone("utc")->format('Y-m-d\TH:i:s');

		$formattedRecords = $records->toCollection()->map(fn(PlayRecordStruct $record) => [
			"player_id"   => $playerId,
			"loaded_at"   => $record->loaded_at,
			"played_at"   => $record->played_at,
			"ended_at"    => $record->ended_at,
			"received_at" => $now,
			"dynamic"     => $record->dynamic_name,
			"version"     => $record->dynamic_version,
			"params"      => json_encode($record->dynamic_params),
			"logs"        => json_encode($record->logs),
		])->all();

		PlayRecord::insert($formattedRecords);

		return new Response([], 200);
	}
}
