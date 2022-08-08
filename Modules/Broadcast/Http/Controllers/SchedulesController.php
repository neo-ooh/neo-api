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
use Illuminate\Support\Facades\DB;
use Neo\Exceptions\BaseException;
use Neo\Http\Controllers\Controller;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleContentAnymoreException;
use Neo\Modules\Broadcast\Exceptions\CannotScheduleIncompleteContentException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentFormatAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\IncompatibleContentLengthAndCampaignException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleBroadcastDaysException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleDatesException;
use Neo\Modules\Broadcast\Exceptions\InvalidScheduleTimesException;
use Neo\Modules\Broadcast\Http\Requests\Schedules\ListPendingSchedulesRequest;
use Neo\Modules\Broadcast\Http\Requests\Schedules\StoreScheduleRequest;
use Neo\Modules\Broadcast\Models\Campaign;
use Neo\Modules\Broadcast\Models\Content;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Utils\ScheduleValidator;

class SchedulesController extends Controller {
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
        // Validate t
        /** @var Content $content */
        $content = Content::query()->with("layout")->findOrFail($request->input("content_id"));

        $startDate     = Carbon::parse($request->input("start_date"), "Y-m-d");
        $startTime     = Carbon::parse($request->input("start_time"), "H:m:s");
        $endDate       = Carbon::parse($request->input("end_date"), "Y-m-d");
        $endTime       = Carbon::parse($request->input("end_time"), "H:m:s");
        $broadcastDays = $request->input("broadcast_days");


        // Prepare the schedule
        $schedule                 = new Schedule();
        $schedule->content_id     = $content->id;
        $schedule->owner_id       = Auth::id();
        $schedule->start_date     = $startDate;
        $schedule->start_time     = $startTime;
        $schedule->end_date       = $endDate;
        $schedule->end_time       = $endTime;
        $schedule->broadcast_days = $broadcastDays;
        $schedule->is_locked      = $request->input('send_for_review');

        $schedulesIds = [];

        // Validate the content and scheduling fit all the selected campaigns
        /** @var Collection<Campaign> $campaigns */
        $campaigns = Campaign::query()->whereIn("id", $request->input("campaigns"))->get();

        $validator = new ScheduleValidator();

        try {
            DB::beginTransaction();

            foreach ($campaigns as $campaign) {
                $validator->validateContentFitCampaign($content, $campaign);
                $validator->validateSchedulingFitCampaign(
                    campaign: $campaign,
                    startDate: $startDate,
                    startTime: $startTime,
                    endDate: $endDate,
                    endTime: $endTime,
                    weekdays: $broadcastDays
                );

                // Schedule ws validated for campaign, store it
                $campaignSchedule              = clone $schedule;
                $campaignSchedule->campaign_id = $campaign->id;
                $campaignSchedule->order       = $campaign->schedules()->count();
                $campaignSchedule->save();

                $campaignSchedule->promote();

                $schedulesIds[] = $campaignSchedule->getKey();
            }

            DB::commit();
        } catch (BaseException $e) {
            DB::rollBack();

            throw $e;
        }

        // Return a response
        return new Response($schedulesIds, 201);
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
                             ->join('contents', 'contents.id', '=', "schedules.content_id")
                             ->join('schedule_details', 'schedule.id', '=', "schedules.id")
                             ->whereIn("schedules.campaign_id", $campaigns)
                             ->where("schedules.is_locked", "=", 1)
                             ->where("schedule_details.is_approved", "<>", 1)
                             ->where('contents.is_approved', '=', false)
                             ->whereNotExists(fn($query) => $query->select(DB::raw(1))
                                                                  ->from('reviews')
                                                                  ->whereRaw('reviews.schedule_id = schedules.id'))
                             ->with([
                                 "campaign",
                                 "campaign.parent:id,name",
                                 "content",
                                 "owner",
                             ])
                             ->get();

        return new Response($schedules);
    }
}
