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
use Ramsey\Uuid\Uuid;

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

        /** @var Collection<Campaign> $libraries */
        $campaigns = Auth::user()?->getAccessibleActors(ids: true);
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
        /** @var Collection<Content> $contents */
        $contents = Content::query()->findMany($request->input("contents"));

        /** @var Collection<Campaign> $campaigns */
        $campaigns = Campaign::query()->whereIn("id", $request->input("campaigns"))->get();

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

        // If there is more than one campaign, apply a batch id to the schedule
        if ($campaigns->count() > 1) {
            $schedule->batch_id = Uuid::uuid4();
        }

        /** @var array<Schedule> $schedules */
        $schedules = [];

        // Validate the content and scheduling fit all the selected campaigns
        $validator = new ScheduleValidator();
        $forceFit  = $request->input("force", false);

        try {
            DB::beginTransaction();

            foreach ($campaigns as $campaign) {
                // List contents that match the campaign
                $campaignContents = [];
                foreach ($contents as $content) {
                    try {
                        $validator->validateContentFitCampaign($content, $campaign);
                        $campaignContents[] = $content;
                    } catch (BaseException) {
                        continue;
                    }
                }

                if (count($campaignContents) === 0) {
                    // If no content match, skip
                    continue;
                }

                // Create the schedule for the campaign
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

                // Schedule is validated for campaign, store it
                $campaignSchedule->campaign_id = $campaign->id;
                $campaignSchedule->order       = $campaign->schedules()->count();
                $campaignSchedule->save();

                // Attach the contents to the schedule
                $campaignSchedule->contents()->attach($contents->pluck("id"));
                $campaignSchedule->broadcast_tags()->sync($broadcastTags);

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

    public function updateWithCampaign(UpdateScheduleRequest $request, Campaign $campaign, Schedule $schedule) {
        return $this->update($request, $schedule);
    }

    /**
     * @param UpdateScheduleRequest $request
     * @param Schedule              $schedule
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

        if (!$schedule->is_locked && $request->input("is_locked", false) && $schedule->end_date->isAfter(Carbon::now())) {
            $schedule->is_locked = true;
            $schedule->locked_at = Date::now();

            // If the schedule start date is set in the past, we move it to today
            if ($schedule->start_date->isBefore(Carbon::now()->startOfDay())) {
                $schedule->start_date = Carbon::now()->startOfDay();
            }

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

    public function destroyWithCampaign(DestroyScheduleRequest $request, Campaign $campaign, Schedule $schedule) {
        return $this->destroy($request, $schedule);
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
            return new Response([]);
        }

        // If the schedule is approved, we check if it has started playing.
        // If so, we set its end-date for yesterday, effectively stopping its broadcast, but keeping it in the
        // `expired` list
        if ($schedule->start_date < Carbon::now()) {
            $schedule->end_date = Carbon::now()->subDay();
            $schedule->save();
            $schedule->promote();

            return new Response([]);
        }

        // Schedule has not started playing, delete it
        $schedule->delete();
        return new Response([]);
    }
}
