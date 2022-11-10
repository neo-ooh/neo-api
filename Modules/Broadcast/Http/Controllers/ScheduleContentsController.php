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
use Neo\Modules\Broadcast\Exceptions\CannotScheduleContentAnymoreException;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleIncompleteContentException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentFormatAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentLengthAndCampaignException;
use Neo\Modules\Broadcast\Http\Requests\ScheduleContents\StoreScheduleContentRequest;
use Neo\Modules\Broadcast\Http\Requests\ScheduleContents\UpdateScheduleContentRequest;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\ScheduleContent;
use Neo\Modules\Broadcast\Utils\ScheduleValidator;

class ScheduleContentsController extends Controller {
    /**
     * @throws IncompatibleContentFormatAndCampaignException
     * @throws IncompatibleContentLengthAndCampaignException
     * @throws CannotScheduleIncompleteContentException
     * @throws CannotScheduleContentAnymoreException
     */
    public function store(StoreScheduleContentRequest $request, Schedule $schedule): Response {
        /** @var Content $content */
        $content = Content::query()->with(["layout"])->findOrFail($request->input("content_id"));

        // First, validate the content fit the campaign
        $validator = new ScheduleValidator();
        $validator->validateContentFitCampaign($content, $schedule->campaign);

        // Content is valid for campaign, Add it to the schedule
        $schedule->contents()->attach($content->getKey());

        /** @var Content $scheduleContent */
        $scheduleContent = $schedule->contents()->where("content_id", "=", $content->getKey())->first();

        $schedule->promote();

        return new Response($scheduleContent->schedule_settings);
    }

    public function update(UpdateScheduleContentRequest $request, Schedule $schedule, ScheduleContent $scheduleContent): Response {
        $scheduleContent->disabled_formats()->sync($request->input("disabled_formats_ids", []));

        $schedule->promote();

        return new Response($scheduleContent->load(["disabled_formats_ids"]));
    }

    public function remove(Schedule $schedule, ScheduleContent $scheduleContent) {
        $scheduleContent->delete();

        $schedule->promote();

        return new Response(["status" => "ok"]);
    }
}
