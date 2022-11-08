<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CampaignsSchedulesController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleContentAnymoreException;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleIncompleteContentException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentFormatAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentLengthAndCampaignException;
use Neo\Modules\Broadcast\Http\Requests\CampaignsSchedules\ListSchedulesRequest;
use Neo\Modules\Broadcast\Http\Requests\CampaignsSchedules\StoreScheduleRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\ReorderSchedulesRequest;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Utils\ScheduleValidator;

class CampaignsSchedulesController extends Controller {
    public function index(ListSchedulesRequest $request, Campaign $campaign): Response {
        return new Response($campaign->schedules->loadPublicRelations());
    }

    /**
     * @throws IncompatibleContentFormatAndCampaignException
     * @throws IncompatibleContentLengthAndCampaignException
     * @throws CannotScheduleIncompleteContentException
     * @throws CannotScheduleContentAnymoreException
     */
    public function store(StoreScheduleRequest $request, Campaign $campaign): Response {
        /** @var Content $content */
        $content = Content::query()->with("layout")->findOrFail($request->input("content_id"));

        $validator = new ScheduleValidator();
        $validator->validateContentFitCampaign($content, $campaign);

        $schedule              = new Schedule();
        $schedule->campaign_id = $campaign->getKey();
        $schedule->owner_id    = Auth::id();
        // Schedule should start today, but not before the campaign start, not after the day before the end of the campaign
        $schedule->start_date     = Carbon::today()->max($campaign->start_date)->min($campaign->end_date->clone()->subDay());
        $schedule->start_time     = $campaign->start_time;
        $schedule->end_date       = $schedule->start_date->clone()
                                                         ->addDays($content->max_schedule_duration ?: 14)
                                                         ->min($campaign->end_date);
        $schedule->end_time       = $campaign->end_time;
        $schedule->broadcast_days = $campaign->broadcast_days;
        $schedule->order          = $request->input("order");
        $schedule->save();

        // Attach the content to the schedule
        $schedule->contents()->attach($content->getKey());

        $schedule->promote();

        return new Response($schedule, 201);
    }

    /**
     * @param ReorderSchedulesRequest $request
     * @param Campaign                $campaign
     *
     * @return Response
     */
    public function reorder(ReorderSchedulesRequest $request, Campaign $campaign): Response {
        $orderedIds = $request->input("schedules");

        // First, make sure the given list of ids corresponds to all the active schedules in the campaign
        $scheduleIds = $campaign->schedules->pluck("id")->all();
        $badIDs      = array_diff($orderedIds, $scheduleIds);

        if (count($badIDs) > 0) {
            throw new InvalidArgumentException("Invalid list of schedules. The following IDs do not belong to this campaign or cannot be reordered:" . implode(", ", $badIDs));
        }

        // Update the order property of each schedule
        foreach ($campaign->schedules as $schedule) {
            $order = array_search($schedule->getKey(), $orderedIds, true);

            if ($order === false) {
                continue;
            }

            $schedule->order = $order;
            $schedule->save();
            $schedule->promote();
        }

        return new Response($campaign->loadPublicRelations());
    }
}
