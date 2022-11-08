<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScheduleContentsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\ScheduleContents\UpdateScheduleContentRequest;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\ScheduleContent;

class ScheduleContentsController extends Controller {
    public function update(UpdateScheduleContentRequest $request, Schedule $schedule, ScheduleContent $scheduleContent): Response {
        $scheduleContent->disabled_formats()->sync($request->input("disabled_formats_ids", []));

        $schedule->promote();

        return new Response($scheduleContent->load(["disabled_formats_ids"]));
    }
}
