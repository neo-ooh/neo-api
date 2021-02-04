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
use Illuminate\Http\Response;
use Neo\Http\Requests\Frames\DestroyFrameRequest;
use Neo\Http\Requests\Frames\StoreFrameRequest;
use Neo\Http\Requests\Frames\UpdateFrameRequest;
use Neo\Models\Frame;

class FramesController extends Controller {
    /**
     * @param StoreFrameRequest $request
     *
     * @return Response
     */
    public function store(StoreFrameRequest $request): Response {
        $frame            = new Frame();
        [
            "layout_id" => $frame->layout_id,
            "name"      => $frame->name,
            "width"     => $frame->width,
            "height"    => $frame->height,
            "type"      => $frame->type,
        ] = $request->validated();
        $frame->save();

        return new Response($frame, 201);
    }

    /**
     * @param UpdateFrameRequest $request
     * @param Frame              $frame
     *
     * @return Response
     */
    public function update(UpdateFrameRequest $request, Frame $frame): Response {
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
