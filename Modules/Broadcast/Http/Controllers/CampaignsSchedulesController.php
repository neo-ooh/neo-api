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

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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
        $schedule->start_date     = $campaign->start_date;
        $schedule->start_time     = $campaign->start_time;
        $schedule->end_date       = $campaign->start_date->clone()
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
        $startDate     = Carbon::parse($request->input("start_date"), "Y-m-d");
        $startTime     = Carbon::parse($request->input("start_time"), "H:m:s");
        $endDate       = Carbon::parse($request->input("end_date"), "Y-m-d");
        $endTime       = Carbon::parse($request->input("end_time"), "H:m:s");
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

        if ($request->input("is_locked", false)) {
            $schedule->is_locked = true;

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

        return new Response($schedule->load("content", "owner"));
    }

    /**
     * @param ReorderSchedulesRequest $request
     * @param Campaign                $campaign
     *
     * @return Response
     */
    public function reorder(ReorderSchedulesRequest $request, Campaign $campaign): Response {
        /** @var Schedule $schedule */
        $schedule = Schedule::query()->findOrFail($request->input("schedule_id"));
        $order    = $request->input("order");

        if ($schedule->order === $order) {
            // Do nothing
            return new Response($campaign->schedules->each(fn(Schedule $schedule) => $schedule->withPublicRelations()));
        }

        if ($order > $campaign->schedules_count) {
            $order = $campaign->schedules_count;
        }

        /** @var Schedule $s */
        foreach ($campaign->schedules as $s) {
            if ($s->is($schedule)) {
                continue;
            }

            if ($s->order >= $schedule->order) {
                --$s->order;
            }

            if ($s->order >= $order) {
                ++$s->order;
            }

            $s->save();
            $s->promote();
        }

        $schedule->order = $order;
        $schedule->save();

        $schedule->promote();

        return new Response($campaign->schedules->each(fn(Schedule $schedule) => $schedule->withPublicRelations()));
    }

    /**
     * @param DestroyScheduleRequest $request
     * @param Schedule               $schedule
     *
     * @return Response
     * @throws Exception
     */
    public function destroy(DestroyScheduleRequest $request, Schedule $schedule): Response {
        // If a schedule has not be reviewed, we want to completely remove it
        if ($schedule->status === ScheduleStatus::Draft || $schedule->status === ScheduleStatus::Pending) {
            $schedule->forceDelete();
        } else {
            $schedule->delete();
        }

        return new Response([]);
    }
}
