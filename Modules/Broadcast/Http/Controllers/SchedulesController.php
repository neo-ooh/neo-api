<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SchedulesController.php
 */

/** @noinspection PhpUnusedParameterInspection */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Exceptions\BaseException;
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
use Neo\Modules\Broadcast\Http\Requests\Schedules\DestroyScheduleRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\ListPendingSchedulesRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\ListSchedulesByIdsRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\StoreScheduleRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\UpdateScheduleRequest;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\ScheduleReview;
use Neo\Modules\Broadcast\Utils\ScheduleValidator;

class SchedulesController extends Controller {

    public function byIds(ListSchedulesByIdsRequest $request): Response {
        $schedules = Schedule::query()->findMany($request->input("ids"));

        return new Response($schedules->loadPublicRelations());
    }

    public function show(ListSchedulesByIdsRequest $request, Schedule $schedule): Response {
        return new Response($schedule->loadPublicRelations());
    }

    /**
     * @param ListPendingSchedulesRequest $request
     *
     * @return Response
     */
    public function pending(ListPendingSchedulesRequest $request): Response {
        // List all accessible schedules pending review
        // A schedule pending review is a schedule who is locked, is not pre-approved, and who doesn't have any reviews

        /** @var Actor $user */
        $user = Auth::user();

        $campaigns = $user->getCampaigns()->pluck('id');
        $schedules = Schedule::query()
                             ->select('schedules.*')
                             ->join('schedule_details', 'schedule_details.schedule_id', '=', "schedules.id")
                             ->whereIn("schedules.campaign_id", $campaigns)
                             ->where("schedules.is_locked", "=", 1)
                             ->where("schedule_details.is_approved", "=", 0)
                             ->where('schedule_details.is_rejected', '=', 0)
                             ->whereNotExists(fn($query) => $query->select(DB::raw(1))
                                                                  ->from('schedule_reviews')
                                                                  ->whereRaw('schedule_reviews.schedule_id = schedules.id'))
                             ->get();

        return new Response($schedules->loadPublicRelations());
    }

    /**
     * @param StoreScheduleRequest $request
     * @return Response
     * @throws BaseException
     * @throws CannotScheduleContentAnymoreException
     * @throws CannotScheduleIncompleteContentException
     * @throws IncompatibleContentFormatAndCampaignException
     * @throws IncompatibleContentLengthAndCampaignException
     * @throws InvalidScheduleBroadcastDaysException
     * @throws InvalidScheduleDatesException
     * @throws InvalidScheduleTimesException
     */
    public function store(StoreScheduleRequest $request): Response {
        /** @var Content $content */
        $content = Content::query()->with("layout")->findOrFail($request->input("content_id"));

        $startDate     = Carbon::createFromFormat("Y-m-d", $request->input("start_date"));
        $startTime     = Carbon::createFromFormat("H:i:s", $request->input("start_time"));
        $endDate       = Carbon::createFromFormat("Y-m-d", $request->input("end_date"));
        $endTime       = Carbon::createFromFormat("H:i:s", $request->input("end_time"));
        $broadcastDays = $request->input("broadcast_days");
        $broadcastTags = $request->input("tags", []);


        // Prepare the schedule
        $schedule                 = new Schedule();
        $schedule->owner_id       = Auth::id();
        $schedule->start_date     = $startDate;
        $schedule->start_time     = $startTime;
        $schedule->end_date       = $endDate;
        $schedule->end_time       = $endTime;
        $schedule->broadcast_days = $broadcastDays;
        $schedule->is_locked      = $request->input('send_for_review');

        /** @var array<Schedule> $schedules */
        $schedules = [];

        // Validate the content and scheduling fit all the selected campaigns
        /** @var Collection<Campaign> $campaigns */
        $campaigns = Campaign::query()->whereIn("id", $request->input("campaigns"))->get();

        $validator = new ScheduleValidator();
        $forceFit  = $request->input("force", false);

        try {
            DB::beginTransaction();

            foreach ($campaigns as $campaign) {
                $validator->validateContentFitCampaign($content, $campaign);

                $campaignSchedule = clone $schedule;

                if ($forceFit) {
                    [$schedule->start_date,
                     $schedule->start_time,
                     $schedule->end_date,
                     $schedule->end_time,
                     $schedule->broadcast_days,
                    ] = $validator->forceFitSchedulingInCampaign(
                        campaign: $campaign,
                        startDate: $startDate,
                        startTime: $startTime,
                        endDate: $endDate,
                        endTime: $endTime,
                        weekdays: $broadcastDays
                    );
                } else {
                    $validator->validateSchedulingFitCampaign(
                        campaign: $campaign,
                        startDate: $startDate,
                        startTime: $startTime,
                        endDate: $endDate,
                        endTime: $endTime,
                        weekdays: $broadcastDays
                    );
                }

                // Schedule ws validated for campaign, store it
                $campaignSchedule->campaign_id = $campaign->id;
                $campaignSchedule->order       = $campaign->schedules()->count();
                $campaignSchedule->save();

                // Attach the content to the schedule
                $campaignSchedule->contents()->attach($content->getKey());

                // Copy tags from content to the schedule
                $campaignSchedule->broadcast_tags()->sync([...$content->broadcast_tags()->allRelatedIds(), ...$broadcastTags]);

                // If the schedule is locked on creation, check if we should auto-approve it or warn someone to approve it
                if ($campaignSchedule->is_locked) {
                    $campaignSchedule->locked_at = Date::now();
                    $campaignSchedule->save();

                    /** @var Actor $user */
                    $user = Auth::user();

                    if ($user->hasCapability(Capability::contents_review)) {
                        $review              = new ScheduleReview();
                        $review->reviewer_id = $user->getKey();
                        $review->schedule_id = $campaignSchedule->getKey();
                        $review->approved    = true;
                        $review->message     = "[auto-approved]";
                        $review->save();
                    }

                    // If not all contents of the schedule are pre-approved, send an email
                    if ($campaignSchedule->contents->some("is_approved", "!==", true) && !Gate::allows(Capability::contents_review->value)) {
                        SendReviewRequestEmail::dispatch($campaignSchedule->id);
                    }
                }

                $schedules[] = $campaignSchedule;
            }

            DB::commit();
        } catch (BaseException $e) {
            DB::rollBack();

            throw $e;
        }

        // All schedules where created successfully, promote them
        foreach ($schedules as $schedule) {
            $schedule->promote();
        }

        return new Response(array_map(static fn(Schedule $schedule) => $schedule->getKey(), $schedules), 201);
    }

    /**
     * @param \Neo\Modules\Broadcast\Http\Requests\Schedules\UpdateScheduleRequest $request
     * @param Schedule                                                             $schedule
     *
     * @return Response
     * @throws InvalidScheduleBroadcastDaysException
     * @throws InvalidScheduleDatesException
     * @throws InvalidScheduleTimesException
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule): Response {
        $campaign      = $schedule->campaign;
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

            if ($schedule->contents->some("is_approved", "!==", true) && !Gate::allows(Capability::contents_review->value)) {
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

        return new Response($schedule->loadPublicRelations());
    }

    /**
     * @param DestroyScheduleRequest $request
     * @param Schedule               $schedule
     *
     * @return Response
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
