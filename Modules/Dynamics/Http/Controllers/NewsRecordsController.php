<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NewsRecordsController.php
 */

namespace Neo\Modules\Dynamics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Dynamics\Http\Requests\NewsRecords\ListNewsRecordsRequest;
use Neo\Modules\Dynamics\Models\NewsRecord;

class NewsRecordsController extends Controller {
	public function index(ListNewsRecordsRequest $request) {
		$records = NewsRecord::query()->whereIn("category", $request->input("categories"))->get();

		return new Response($records);
	}
}
