<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - FramesController.php
 */

namespace Neo\Http\Controllers;

use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Neo\Http\Requests\Frames\DestroyFrameRequest;
use Neo\Http\Requests\Frames\StoreFrameRequest;
use Neo\Http\Requests\Frames\UpdateFrameRequest;
use Neo\Models\Format;
use Neo\Models\Frame;

class FramesController extends Controller {
    /**
     * @param StoreFrameRequest $request
     * @param Format            $format
     *
     * @return ResponseFactory|Response
     */
    public function store(StoreFrameRequest $request, Format $format) {
        $frame            = new Frame();
        $frame->format_id = $format->id;
        [
            "name"   => $frame->name,
            "width"  => $frame->width,
            "height" => $frame->height,
            "type"   => $frame->type,
        ] = $request->validated();
        $frame->save();

        return new Response($frame, 201);
    }

    /**
     * @param UpdateFrameRequest $request
     * @param Format             $format
     * @param Frame              $frame
     *
     * @return ResponseFactory|Response
     * @noinspection PhpUnusedParameterInspection
     */
    public function update(UpdateFrameRequest $request, Format $format, Frame $frame) {
        [
            "name"   => $frame->name,
            "width"  => $frame->width,
            "height" => $frame->height,
            "type"   => $frame->type,
        ] = $request->validated();
        $frame->save();

        return new Response($frame);
    }

    /**
     * @param DestroyFrameRequest $request
     * @param Format              $format
     * @param Frame               $frame
     *
     * @return ResponseFactory|Response
     * @throws Exception
     * @noinspection PhpUnusedParameterInspection
     */
    public function destroy(DestroyFrameRequest $request, Format $format, Frame $frame) {
        $frame->delete();

        return new Response([]);
    }
}
