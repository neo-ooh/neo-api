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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Neo\Http\Requests\Frames\DestroyFrameRequest;
use Neo\Http\Requests\Frames\StoreFrameRequest;
use Neo\Http\Requests\Frames\UpdateFrameRequest;
use Neo\Models\Frame;
use Neo\Models\FrameSettingsBroadSign;
use Neo\Models\FrameSettingsPiSignage;
use Neo\Services\Broadcast\Broadcaster;

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

        $broadcasters = $frame->layout->format->display_types->load("broadcaster_connections")->pluck("broadcaster_connections.broadcaster")->values()->unique();

        foreach ($broadcasters as $broadcaster) {
            switch ($broadcaster) {
                case Broadcaster::BROADSIGN:
                    $settings = new FrameSettingsBroadSign();
                    $settings->frame_id = $frame->id;
                    $settings->criteria_id = $request->input("criteria_id");
                    $settings->save();
                    break;
                case Broadcaster::PISIGNAGE:
                    $settings = new FrameSettingsPiSignage();
                    $settings->frame_id = $frame->id;
                    $settings->zone_name = $request->input("zone_name");
                    $settings->save();
                    break;
            }
        }

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
            "name"        => $frame->name,
            "width"       => $frame->width,
            "height"      => $frame->height,
        ] = $request->validated();
        $frame->save();

        $broadcasters = $frame->layout->format->display_types->load("broadcaster_connections")->pluck("broadcaster_connections.broadcaster")->values()->unique();

        foreach ($broadcasters as $broadcaster) {
            switch ($broadcaster) {
                case Broadcaster::BROADSIGN:
                    $settings = FrameSettingsBroadSign::findOrNew($frame->id);
                    $settings->criteria_id = $request->input("criteria_id");
                    $settings->save();
                    break;
                case Broadcaster::PISIGNAGE:
                    $settings = FrameSettingsPiSignage::findOrNew($frame->id);
                    $settings->zone_name = $request->input("zone_name");
                    $settings->save();
                    break;
            }
        }

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
