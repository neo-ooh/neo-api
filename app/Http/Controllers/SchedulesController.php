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

namespace Neo\Http\Controllers;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Exceptions\InvalidBroadcastServiceException;
use Neo\Http\Requests\Schedules\DestroyScheduleRequest;
use Neo\Http\Requests\Schedules\InsertScheduleRequest;
use Neo\Http\Requests\Schedules\ListPendingSchedulesRequest;
use Neo\Http\Requests\Schedules\ReorderScheduleRequest;
use Neo\Http\Requests\Schedules\StoreScheduleRequest;
use Neo\Http\Requests\Schedules\UpdateScheduleRequest;
use Neo\Jobs\SendReviewRequestEmail;
use Neo\Models\Campaign;
use Neo\Models\Content;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\Broadcast;

class SchedulesController extends Controller {
    /**
     * @param StoreScheduleRequest $request
     * @param Campaign             $campaign
     *
     * @return Response
     * @throws InvalidBroadcastServiceException
     */
    public function store(StoreScheduleRequest $request, Campaign $campaign): Response {
        // User has access to both the campaign and the piece of content, we now need to check
        // if it is possible to schedule the later in the former.
        /** @var Content $content */
        $content = Content::query()->with("layout")->findOrFail($request->validated()["content_id"]);

        $this->validateContent($content, $campaign);

        // Are the schedules date contained in the campaign ones ?
        $startDate = Carbon::parse($request->input("schedule_start"));
        $endDate   = Carbon::parse($request->input("schedule_end"));
        if ($startDate->isAfter($endDate)) {
            return new Response(["Incorrect schedules dates"], 422);
        }

        if ($startDate->isBefore($campaign->start_date) || $endDate->isAfter($campaign->end_date)) {
            return new Response(["Invalid scheduling dates for this campaign"], 422);
        }

        // Looks like we are good!
        $schedule              = new Schedule();
        $schedule->content_id  = $content->id;
        $schedule->campaign_id = $campaign->id;
        $schedule->owner_id    = Auth::id();
        $schedule->start_date  = $startDate;
        $schedule->end_date    = $endDate;
        $schedule->order       = $campaign->schedules()->count();
        $schedule->locked      = $request->validated()['send_for_review'];
        $schedule->is_approved = $content->is_approved;
        $schedule->save();
        $schedule->refresh();

        // Replicate the schedule in BroadSign
        Broadcast::network($campaign->network_id)->createSchedule($schedule->id, Auth::id());

        // Return a response
        return new Response($schedule->loadMissing(["content"]), 201);
    }

    /**
     * @param Content  $content
     * @param Campaign $campaign
     *
     * @return Response|null
     */
    private function validateContent(Content $content, Campaign $campaign): ?Response {
        // Is the content full ?
        if ($content->layout->frames->count() !== $content->creatives()->count()) {
            return new Response(["An incomplete content cannot be scheduled"], 422);
        }

        // Can the content be scheduled ?
        if ($content->scheduling_times !== 0 && $content->schedules_count >= $content->scheduling_times) {
            return new Response(["This content cannot be scheduled again"], 422);
        }

        // Does the schedule has the correct format
        if ($content->layout->format_id !== $campaign->format_id) {
            return new Response(["Content format does not fit in this campaign"], 422);
        }

        // Does the schedule has an acceptable media type
        // Check that all creatives in the content match the campaign media types

        // Is the content the correct length
        if ($content->duration > 0 && $content->duration > $campaign->schedules_max_length) {
            return new Response(["This content has not the correct length ($content->duration > $campaign->schedules_max_length)"],
                422);
        }

        return null;
    }

    /**
     * @throws InvalidBroadcastServiceException
     */
    public function insert(InsertScheduleRequest $request, Campaign $campaign): Response {
        // User has access to both the campaign and the piece of content, we now need to check
        // if it is possible to schedule the later in the former.
        /** @var Content $content */
        $content = Content::query()->findOrFail($request->validated()["content_id"]);

        $result = $this->validateContent($content, $campaign);

        if (!is_null($result)) {
            return $result;
        }

        $startDate = $campaign->start_date->isBefore(now()) ? now() : $campaign->start_date;
        $startDate->setHour($campaign->start_date->hour);
        $startDate->setMinutes($campaign->start_date->minute);

        $endDate = $campaign->end_date;
        $temp    = $startDate->copy()->addDays($content->scheduling_duration);

        if ($content->scheduling_duration !== 0 && $temp->isBefore($campaign->end_date)) {
            $endDate = $temp;
        }

        $endDate->setHour($campaign->end_date->hour);
        $endDate->setMinutes($campaign->end_date->minute);

        // Validate order
        $order = $request->validated()["order"];
        if ($order !== $campaign->schedules_count) {
            // Already uploaded schedules need reordering
            foreach ($campaign->schedules as $s) {
                if ($s->order >= $order) {
                    $s->increment('order', 1);
                }
            }
        }

        // Looks like we are good!
        $schedule              = new Schedule();
        $schedule->content_id  = $content->id;
        $schedule->campaign_id = $campaign->id;
        $schedule->owner_id    = Auth::id();
        $schedule->start_date  = $startDate;
        $schedule->end_date    = $endDate;
        $schedule->order       = $order;
        $schedule->locked      = false;
        $schedule->is_approved = $content->is_approved;
        $schedule->save();
        $schedule->refresh();

        // Replicate the schedule in BroadSign
        Broadcast::network($schedule->campaign->network_id)->createSchedule($schedule->id, Auth::id());

        return new Response($schedule->loadMissing(["content", "reviews"]), 201);
    }

    /**
     * @param UpdateScheduleRequest $request
     * @param Schedule              $schedule
     *
     * @return Response
     * @throws InvalidBroadcastServiceException
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule): Response {
        $startDate = new Carbon($request->input("start_date"));
        $endDate   = new Carbon($request->input("end_date"));

        // Make sure the schedules dates can be edited
        if ($schedule->status !== 'draft' && !Gate::allows(Capability::contents_review)) {
            return new Response(["You are not authorized to edit this schedule"], 403);
        }

        // Validates the new dates still fit the campaign
        if ($startDate > $endDate) {
            return new Response(["Incorrect schedules dates"], 422);
        }

        if ($startDate->isBefore($schedule->campaign->start_date)) {
            $startDate = $schedule->campaign->start_date;
        }

        if ($endDate->isAfter($schedule->campaign->end_date)) {
            $endDate = $schedule->campaign->end_date;
        }

        // We are good, update the schedule
        $schedule->start_date = $startDate;
        $schedule->end_date   = $endDate;

        if ($request->has("locked") && $request->validated()["locked"] === true) {
            $schedule->locked = true;

            if (Auth::user()->hasCapability(Capability::contents_review())) {
                $schedule->is_approved = true;
            }

            if (!$schedule->content->is_approved && !Gate::allows(Capability::contents_review)) {
                SendReviewRequestEmail::dispatch($schedule->id);
            }
        }

        $schedule->save();
        $schedule->refresh();

        // Propagate the update to the associated BroadSign Schedule
        Broadcast::network($schedule->campaign->network_id)->updateSchedule($schedule->id);

        return new Response($schedule->load("content", "owner"));
    }

    /**
     * @param ReorderScheduleRequest $request
     * @param Campaign               $campaign
     *
     * @return Response
     * @throws InvalidBroadcastServiceException
     */
    public function reorder(ReorderScheduleRequest $request, Campaign $campaign): Response {
        ["schedule_id" => $scheduleID, "order" => $order] = $request->validated();

        /** @var Schedule $schedule */
        $schedule = Schedule::query()->findOrFail($scheduleID);

        if ($schedule->order === $order) {
            // Do nothing
            return new Response($campaign->loadMissing([
                "format",
                "locations",
                "owner",
                "shares",
                "schedules",
                "schedules.content",
                "trashedSchedules",
                "trashedSchedules.content"])->append("related_libraries"));
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
                $s->decrement('order');
            }

            if ($s->order >= $order) {
                $s->increment('order');
            }

            $s->save();
        }

        $schedule->order = $order;
        $schedule->save();

        Broadcast::network($campaign->network_id)->updateCampaignSchedulesOrder($campaign->id);

        return (new CampaignsController())->show($campaign);
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
        if ($schedule->status === 'draft' || $schedule->status === 'pending') {
            $schedule->forceDelete();
        } else {
            $schedule->delete();
        }

        return new Response([]);
    }

    /**
     * @param ListPendingSchedulesRequest $request
     *
     * @return Response
     */
    public function pending(ListPendingSchedulesRequest $request): Response {
        // List all accessible schedules pending review
        // A schedule pending review is a schedule who is locked, is not pre-approved, and who doesn't have any reviews

        $campaigns = Auth::user()->getCampaigns()->pluck('id');
        $schedules = Schedule::query()
                             ->whereIn("campaign_id", $campaigns)
                             ->where("locked", "=", 1)
                             ->where("schedules.is_approved", "<>", 1)
                             ->join('contents', 'contents.id', '=', "content_id")
                             ->where('contents.is_approved', '=', false)
                             ->whereNotExists(fn($query) => $query->select(DB::raw(1))
                                                                  ->from('reviews')
                                                                  ->whereRaw('reviews.schedule_id = schedules.id'))
                             ->with([
                                 "campaign",
                                 "campaign.owner:id,name",
                                 "content",
                                 "owner",
                             ])
                             ->get();

        return new Response($schedules);
    }
}
