<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadSignSeparationsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\BroadSignSeparations\ListBroadSignSeparationsRequest;
use Neo\Http\Requests\BroadSignSeparations\ShowBroadSignSeparationsRequest;
use Neo\Http\Requests\BroadSignSeparations\StoreBroadSignSeparationsRequest;
use Neo\Http\Requests\BroadSignSeparations\UpdateBroadSignSeparationsRequest;
use Neo\Models\BroadSignSeparation;

class BroadSignSeparationsController extends Controller {
    public function index(ListBroadSignSeparationsRequest $request) {
        return new Response(BroadSignSeparation::query()->orderBy("name")->get()->values());
    }

    public function show(ShowBroadSignSeparationsRequest $request, BroadSignSeparation $separation) {
        return new Response($separation);
    }

    public function store(StoreBroadSignSeparationsRequest $request) {
        $separation = new BroadSignSeparation();
        [
            "name"       => $separation->name,
            "broadsign_separation_id" => $separation->broadsign_separation_id,
        ] = $request->validated();
        $separation->save();

        return new Response($separation, 201);
    }

    public function update(UpdateBroadSignSeparationsRequest $request, BroadSignSeparation $separation) {
        [
            "name"       => $separation->name,
            "broadsign_separation_id" => $separation->broadsign_separation_id,
        ] = $request->validated();
        $separation->save();

        return new Response($separation, 200);
    }

    public function destroy(BroadSignSeparation $separation) {
        // Todo: Wait for trigger implementation in Campaigns for this method
    }
}
