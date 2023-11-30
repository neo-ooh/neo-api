<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NewsRecordsCategoriesController.php
 */

namespace Neo\Modules\Dynamics\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Dynamics\Http\Requests\NewsCategories\ListCategoriesRequest;
use Neo\Modules\Dynamics\Models\NewsRecord;

class NewsRecordsCategoriesController extends Controller {
	public function index(ListCategoriesRequest $request) {
		return new Response(DB::query()
		                      ->from((new NewsRecord())->getTable())
		                      ->select(["category"])
		                      ->distinct()
		                      ->orderBy("category")
		                      ->get());
	}
}
