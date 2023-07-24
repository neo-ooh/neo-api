<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayTypeFramesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\DisplayTypesFrames\DestroyDisplayTypeFrameRequest;
use Neo\Modules\Broadcast\Http\Requests\DisplayTypesFrames\ListDisplayTypeFramesRequest;
use Neo\Modules\Broadcast\Http\Requests\DisplayTypesFrames\ShowDisplayTypeFrameRequest;
use Neo\Modules\Broadcast\Http\Requests\DisplayTypesFrames\StoreDisplayTypeFrameRequest;
use Neo\Modules\Broadcast\Http\Requests\DisplayTypesFrames\UpdateDisplayTypeFrameRequest;
use Neo\Modules\Broadcast\Models\DisplayType;
use Neo\Modules\Broadcast\Models\DisplayTypeFrame;

class DisplayTypeFramesController extends Controller {
    public function index(ListDisplayTypeFramesRequest $request, DisplayType $displayType) {
        return new Response($displayType->crop_frames);
    }

    public function store(StoreDisplayTypeFrameRequest $request, DisplayType $displayType) {
        $frame                  = new DisplayTypeFrame();
        $frame->display_type_id = $displayType->getKey();
        $frame->name            = $request->input("name");
        $frame->pos_x           = $request->input("pos_x");
        $frame->pos_y           = $request->input("pos_y");
        $frame->width           = $request->input("width");
        $frame->height          = $request->input("height");
        $frame->save();

        return new Response($frame, 201);
    }

    public function show(ShowDisplayTypeFrameRequest $request, DisplayType $displayType, DisplayTypeFrame $displayTypeFrame) {
        return $displayTypeFrame;
    }

    public function update(UpdateDisplayTypeFrameRequest $request, DisplayType $displayType, DisplayTypeFrame $displayTypeFrame) {
        $displayTypeFrame->name   = $request->input("name");
        $displayTypeFrame->pos_x  = $request->input("pos_x");
        $displayTypeFrame->pos_y  = $request->input("pos_y");
        $displayTypeFrame->width  = $request->input("width");
        $displayTypeFrame->height = $request->input("height");
        $displayTypeFrame->save();

        return new Response($displayTypeFrame);
    }

    public function destroy(DestroyDisplayTypeFrameRequest $request, DisplayType $displayType, DisplayTypeFrame $displayTypeFrame) {
        $displayTypeFrame->delete();

        return new Response(["status" => "ok"]);
    }
}
