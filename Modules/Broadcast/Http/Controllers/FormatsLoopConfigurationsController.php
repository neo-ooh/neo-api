<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FormatsLoopConfigurationsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\FormatsLoopConfigurations\SyncLoopConfigurationsRequest;
use Neo\Modules\Broadcast\Models\Format;

class FormatsLoopConfigurationsController extends Controller {
    public function sync(SyncLoopConfigurationsRequest $request, Format $format): Response {
        $format->loop_configurations()->sync($request->input("loop_configurations", []));

        return new Response($format->loop_configurations);
    }
}
