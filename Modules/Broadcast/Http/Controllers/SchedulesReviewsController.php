<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SchedulesReviewsController.php
 */

namespace Neo\Modules\Broadcast\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Exceptions\InvalidBroadcastServiceException;
use Neo\Http\Controllers\Controller;
use Neo\Http\Requests\Reviews\StoreReviewRequest;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\ScheduleReview;
use Neo\Services\Broadcast\Broadcast;

class SchedulesReviewsController extends Controller {
    /**
     * @param StoreReviewRequest                     $request
     * @param \Neo\Modules\Broadcast\Models\Schedule $schedule
     *
     * @return Response
     * @throws InvalidBroadcastServiceException
     */
    public function store(StoreReviewRequest $request, Schedule $schedule) {
        $review              = new ScheduleReview();
        $review->schedule_id = $schedule->id;
        $review->reviewer_id = Auth::id();
        [
            "approved" => $review->approved,
            "message"  => $review->message,
        ] = $request->validated();
        $review->save();

        $schedule->is_approved = $review->approved;
        $schedule->save();
        $schedule->refresh();

        // Update the schedule in BroadSign to reflect the new status
        if ($schedule->status === Schedule::STATUS_APPROVED || $schedule->status === Schedule::STATUS_LIVE) {
            Broadcast::network($schedule->campaign->network_id)->enableSchedule($schedule->id);
        } else {
            Broadcast::network($schedule->campaign->network_id)->disableSchedule($schedule->id);
        }

        return new Response($schedule->load("content", "owner:id,name", "campaign", "campaign.owner:id,name"), 201);
    }
}
