<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
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
        $format->layouts()->sync($request->input("layouts"));

        return new Response($format->display_types);
    }
}