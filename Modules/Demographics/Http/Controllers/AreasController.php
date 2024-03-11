<?php
/*
 * Copyright 2024 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AreasController.php
 */

namespace Neo\Modules\Demographics\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Demographics\Http\Requests\Areas\ListAreasRequest;
use Neo\Modules\Demographics\Models\Area;

class AreasController extends Controller {
    public function index(ListAreasRequest $request) {
        $query = Area::query()
                     ->where("type_id", "=", $request->input("type"))
                     ->orderBy("code");

        $totalCount = $query->clone()->count();

        $page  = $request->input("page", 1);
        $count = $request->input("count", 500);
        $from  = ($page - 1) * $count;
        $to    = ($page * $count) - 1;

        $query->limit($count)
              ->offset($from);

        return new Response($query->get()->loadPublicRelations(), 200, [
            "Content-Range" => "items $from-$to/$totalCount",
        ]);
    }
}
