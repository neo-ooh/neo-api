<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FramesController.php
 */

namespace Neo\Http\Controllers;

use Exception;
use Illuminate\Http\Response;
use Neo\Http\Requests\Frames\DestroyFrameRequest;
use Neo\Http\Requests\Frames\StoreFrameRequest;
use Neo\Http\Requests\Frames\UpdateFrameRequest;
use Neo\Modules\Broadcast\Models\Frame;
use Neo\Modules\Broadcast\Models\FrameSettingsBroadSign;
use Neo\Modules\Broadcast\Models\FrameSettingsPiSignage;

class FramesController extends Controller {
    /**
     * @param StoreFrameRequest $request
     *
     * @return Response
     */
    public function store(StoreFrameRequest $request): Response {
        $frame            = new Frame();
        $frame->layout_id = $request->input("layout_id");
        $frame->name      = $request->input("name");
        $frame->width     = $request->input("width");
        $frame->height    = $request->input("height");
        $frame->save();

        if($request->has("criteria_id")) {
            $settings = new FrameSettingsBroadSign();
            $settings->frame_id = $frame->id;
            $settings->criteria_id = $request->input("criteria_id");
            $settings->save();
        }

        if($request->has("zone_name")) {
            $settings = new FrameSettingsPiSignage();
            $settings->frame_id = $frame->id;
            $settings->zone_name = $request->input("zone_name");
            $settings->save();
        }

        return new Response($frame->refresh(), 201);
    }

    /**
     * @param UpdateFrameRequest $request
     * @param Frame              $frame
     *
     * @return Response
     */
    public function update(UpdateFrameRequest $request, Frame $frame): Response {
        [
            "name"        => $frame->name,
            "width"       => $frame->width,
            "height"      => $frame->height,
        ] = $request->validated();
        $frame->save();

        if($request->has("criteria_id")) {
            $settings = FrameSettingsBroadSign::firstOrNew(["frame_id" => $frame->id]);
            $settings->criteria_id = $request->input("criteria_id");
            $settings->save();
        }

        if($request->has("zone_name")) {
            $settings = FrameSettingsPiSignage::firstOrNew(["frame_id" => $frame->id]);
            $settings->zone_name = $request->input("zone_name");
            $settings->save();
        }

        return new Response($frame->refresh());
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