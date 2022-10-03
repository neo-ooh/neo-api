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
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;
use Neo\Enums\Capability;
use Neo\Http\Controllers\Controller;
use Neo\Jobs\SendReviewRequestEmail;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Enums\ScheduleStatus;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleContentAnymoreException;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleIncompleteContentException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentFormatAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentLengthAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleBroadcastDaysException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleDatesException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleTimesException;
use Neo\Modules\Broadcast\Http\Requests\CampaignsSchedules\ListSchedulesRequest;
use Neo\Modules\Broadcast\Http\Requests\CampaignsSchedules\StoreScheduleRequest;
use Neo\Modules\Broadcast\Http\Requests\CampaignsSchedules\UpdateScheduleRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\DestroyScheduleRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\ReorderSchedulesRequest;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\ScheduleReview;
use Neo\Modules\Broadcast\Utils\ScheduleValidator;

class CampaignsSchedulesController extends Controller {
    public function index(ListSchedulesRequest $request, Campaign $campaign): Response {
        return new Response($campaign->schedules->each(fn(Schedule $schedule) => $schedule->withPublicRelations()));
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

        $schedule                 = new Schedule();
        $schedule->campaign_id    = $campaign->getKey();
        $schedule->content_id     = $content->id;
        $schedule->owner_id       = Auth::id();
        $schedule->start_date     = Carbon::today()->max($campaign->start_date);
        $schedule->start_time     = $campaign->start_time;
        $schedule->end_date       = $schedule->start_date->clone()
                                                         ->addDays($content->max_schedule_duration ?: 14)
                                                         ->min($campaign->end_date);
        $schedule->end_time       = $campaign->end_time;
        $schedule->broadcast_days = $campaign->broadcast_days;
        $schedule->order          = $request->input("order");
        $schedule->save();

        $schedule->promote();

        return new Response($schedule, 201);
    }

    /**
     * @param UpdateScheduleRequest $request
     * @param Campaign              $campaign
     * @param Schedule              $schedule
     *
     * @return Response
     * @throws InvalidScheduleBroadcastDaysException
     * @throws InvalidScheduleDatesException
     * @throws InvalidScheduleTimesException
     */
    public function update(UpdateScheduleRequest $request, Campaign $campaign, Schedule $schedule): Response {
        $startDate     = Carbon::createFromFormat("Y-m-d", $request->input("start_date"))->startOfDay();
        $startTime     = Carbon::createFromFormat("H:i:s", $request->input("start_time"));
        $endDate       = Carbon::createFromFormat("Y-m-d", $request->input("end_date"))->startOfDay();
        $endTime       = Carbon::createFromFormat("H:i:s", $request->input("end_time"));
        $broadcastDays = $request->input("broadcast_days");

        // Make sure the schedules dates can be edited
        if ($schedule->status !== ScheduleStatus::Draft && !Gate::allows(Capability::contents_review->value)) {
            return new Response(["You are not authorized to edit this schedule"], 403);
        }

        $validator = new ScheduleValidator();
        $validator->validateSchedulingFitCampaign(
            campaign: $campaign,
            startDate: $startDate,
            startTime: $startTime,
            endDate: $endDate,
            endTime: $endTime,
            weekdays: $broadcastDays
        );

        // We are good, update the schedule
        $schedule->start_date     = $startDate;
        $schedule->start_time     = $startTime;
        $schedule->end_date       = $endDate;
        $schedule->end_time       = $endTime;
        $schedule->broadcast_days = $broadcastDays;

        if (!$schedule->is_locked && $request->input("is_locked", false)) {
            $schedule->is_locked = true;
            $schedule->locked_at = Date::now();

            /** @var Actor $user */
            $user = Auth::user();

            if ($user->hasCapability(Capability::contents_review)) {
                $review              = new ScheduleReview();
                $review->reviewer_id = $user->getKey();
                $review->schedule_id = $schedule->getKey();
                $review->approved    = true;
                $review->message     = "[auto-approved]";
                $review->save();
            }

            if (!$schedule->content->is_approved && !Gate::allows(Capability::contents_review->value)) {
                SendReviewRequestEmail::dispatch($schedule->id);
            }
        }

        $schedule->save();

        if (Gate::allows(Capability::schedules_tags->value)) {
            $schedule->broadcast_tags()->sync($request->input("tags"));
        }

        $schedule->refresh();

        // Propagate the update to the associated BroadSign Schedule
        $schedule->promote();

        return new Response($schedule->withPublicRelations());
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

        return new Response($campaign->withPublicRelations());
    }

    /**
     * @param DestroyScheduleRequest $request
     * @param Campaign               $campaign
     * @param Schedule               $schedule
     *
     * @return Response
     */
    public function destroy(DestroyScheduleRequest $request, Campaign $campaign, Schedule $schedule): Response {
        // If a schedule has not be reviewed, we want to completely remove it
        if ($schedule->status === ScheduleStatus::Draft || $schedule->status === ScheduleStatus::Pending) {
            $schedule->forceDelete();
        } else {
            $schedule->delete();
        }

        return new Response([]);
    }
}
