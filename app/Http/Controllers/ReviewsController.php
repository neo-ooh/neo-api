<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ReviewsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\Http\Requests\Reviews\StoreReviewRequest;
use Neo\Models\Review;
use Neo\Models\Schedule;
use Neo\Services\Broadcast\Broadcast;

class ReviewsController extends Controller {
    /**
     * @param StoreReviewRequest $request
     * @param Schedule           $schedule
     *
     * @return Response
     * @throws \Neo\Exceptions\InvalidBroadcastServiceException
     */
    public function store(StoreReviewRequest $request, Schedule $schedule) {
        $review              = new Review();
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
        if($schedule->status === Schedule::STATUS_APPROVED || $schedule->status  === Schedule::STATUS_LIVE) {
            Broadcast::network($schedule->campaign->network_id)->enableSchedule($schedule->id);
        }  else {
            Broadcast::network($schedule->campaign->network_id)->disableSchedule($schedule->id);
        }

        return new Response($schedule->load("content", "owner:id,name", "campaign", "campaign.owner:id,name"), 201);
    }
}
