<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FormatCropFramesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\FormatsCropFrames\DestroyFormatCropFrameRequest;
use Neo\Modules\Broadcast\Http\Requests\FormatsCropFrames\ListFormatsCropFramesRequest;
use Neo\Modules\Broadcast\Http\Requests\FormatsCropFrames\ShowFormatCropFrameRequest;
use Neo\Modules\Broadcast\Http\Requests\FormatsCropFrames\StoreFormatCropFrameRequest;
use Neo\Modules\Broadcast\Http\Requests\FormatsCropFrames\UpdateFormatCropFrameRequest;
use Neo\Modules\Broadcast\Models\Format;
use Neo\Modules\Broadcast\Models\FormatCropFrame;

class FormatCropFramesController extends Controller {
    public function index(ListFormatsCropFramesRequest $request, Format $format) {
        return new Response($format->crop_frames()->get());
    }

    public function store(StoreFormatCropFrameRequest $request, Format $format) {
        $cropFrame                        = new FormatCropFrame();
        $cropFrame->format_id             = $format->getKey();
        $cropFrame->display_type_frame_id = $request->input("display_type_frame_id");
        $cropFrame->pos_x                 = $request->input("pos_x");
        $cropFrame->pos_y                 = $request->input("pos_y");
        $cropFrame->scale                 = $request->input("scale");
        $cropFrame->aspect_ratio          = $request->input("aspect_ratio");

        $cropFrame->save();

        return new Response($cropFrame, 201);
    }

    public function show(ShowFormatCropFrameRequest $request, Format $format, FormatCropFrame $formatCropFrame) {
        return new Response($formatCropFrame);
    }

    public function update(UpdateFormatCropFrameRequest $request, Format $format, FormatCropFrame $formatCropFrame) {
        $formatCropFrame->pos_x        = $request->input("pos_x");
        $formatCropFrame->pos_y        = $request->input("pos_y");
        $formatCropFrame->scale        = $request->input("scale");
        $formatCropFrame->aspect_ratio = $request->input("aspect_ratio");

        $formatCropFrame->save();

        return new Response($formatCropFrame);
    }

    public function destroy(DestroyFormatCropFrameRequest $request, Format $format, FormatCropFrame $formatCropFrame) {
        $formatCropFrame->delete();

        return new Response(["status" => "ok"]);
    }
}
