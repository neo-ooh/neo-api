<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FramesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Exception;
use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\Frames\DestroyFrameRequest;
use Neo\Modules\Broadcast\Http\Requests\Frames\ListFramesRequest;
use Neo\Modules\Broadcast\Http\Requests\Frames\StoreFrameRequest;
use Neo\Modules\Broadcast\Http\Requests\Frames\UpdateFrameRequest;
use Neo\Modules\Broadcast\Models\Frame;
use Neo\Modules\Broadcast\Models\Layout;

class FramesController extends Controller {
    /**
     * @param ListFramesRequest $request
     * @param Layout            $layout
     * @return Response
     */
    public function index(ListFramesRequest $request, Layout $layout): Response {
        return new Response($layout->frames->load("broadcast_tags"));
    }

    /**
     * @param StoreFrameRequest $request
     * @param Layout            $layout
     * @return Response
     */
    public function store(StoreFrameRequest $request, Layout $layout): Response {
        $frame            = new Frame();
        $frame->layout_id = $layout->getKey();
        $frame->name      = $request->input("name");
        $frame->width     = $request->input("width");
        $frame->height    = $request->input("height");
        $frame->save();

        $frame->broadcast_tags()->sync($request->input("tags"));

        return new Response($frame->load("tags")->refresh(), 201);
    }

    /**
     * @param UpdateFrameRequest $request
     * @param Frame              $frame
     *
     * @return Response
     */
    public function update(UpdateFrameRequest $request, Layout $layout, Frame $frame): Response {
        [
            "name"   => $frame->name,
            "width"  => $frame->width,
            "height" => $frame->height,
        ] = $request->validated();
        $frame->save();

        $frame->broadcast_tags()->sync($request->input("broadcast_tags"));

        return new Response($frame->load("broadcast_tags")->refresh());
    }

    /**
     * @param DestroyFrameRequest $request
     * @param Frame               $frame
     *
     * @return Response
     * @throws Exception
     */
    public function destroy(DestroyFrameRequest $request, Frame $frame): Response {
        $frame->delete();

        return new Response([]);
    }
}
