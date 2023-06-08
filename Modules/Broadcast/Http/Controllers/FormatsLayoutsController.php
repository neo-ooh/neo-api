<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FormatsLayoutsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\FormatsLayouts\SyncFormatLayoutsRequest;
use Neo\Modules\Broadcast\Models\Format;

class FormatsLayoutsController extends Controller {
    public function sync(SyncFormatLayoutsRequest $request, Format $format): Response {
        $format->layouts()->sync(collect($request->input("layouts"))
                                     ->mapWithKeys(fn(array $layout) => [$layout["layout_id"] => ["is_fullscreen" => $layout["is_fullscreen"]]])
        );

        // Make sure the format's main_layout_id is still part of the associated layouts
        $format->main_layout_id = $format->layouts()
                                         ->where("id", "=", $format->main_layout_id)
                                         ->exists() ? $format->main_layout_id : null;
        $format->save();

        return new Response($format->layouts);
    }
}
