<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LayoutsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\Layouts\DestroyLayoutRequest;
use Neo\Modules\Broadcast\Http\Requests\Layouts\ListLayoutsRequest;
use Neo\Modules\Broadcast\Http\Requests\Layouts\ShowLayoutRequest;
use Neo\Modules\Broadcast\Http\Requests\Layouts\StoreLayoutRequest;
use Neo\Modules\Broadcast\Http\Requests\Layouts\UpdateLayoutRequest;
use Neo\Modules\Broadcast\Models\Layout;

class LayoutsController extends Controller {
    public function index(ListLayoutsRequest $request): Response {
        $layouts = Layout::query()->orderBy("name_en")->orderBy("name_fr")->get();

        return new Response($layouts->loadPublicRelations());
    }

    public function store(StoreLayoutRequest $request): Response {
        $layout          = new Layout();
        $layout->name_en = $request->input("name_en");
        $layout->name_fr = $request->input("name_fr");
        $layout->save();

        $layout->broadcast_tags()->sync($request->input("tags"));

        return new Response($layout->load("frames"), 201);
    }

    public function show(ShowLayoutRequest $request, Layout $layout): Response {
        $layout->loadPublicRelations();

        return new Response($layout);
    }

    public function update(UpdateLayoutRequest $request, Layout $layout): Response {
        $layout->name_en = $request->input("name_en");
        $layout->name_fr = $request->input("name_fr");
        $layout->save();

        $layout->broadcast_tags()->sync($request->input("tags"));

        return new Response($layout->load("broadcast_tags"));
    }

    public function destroy(DestroyLayoutRequest $request, Layout $layout): Response {
        $layout->frames->each(fn($frame) => $frame->delete());
        $layout->delete();

        return new Response();
    }
}
