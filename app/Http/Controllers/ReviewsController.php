<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - ReviewsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Neo\BroadSign\Jobs\Schedules\UpdateBroadSignScheduleStatus;
use Neo\Http\Requests\Reviews\StoreReviewRequest;
use Neo\Models\Review;
use Neo\Models\Schedule;

class ReviewsController extends Controller {
    /**
     * @param StoreReviewRequest $request
     * @param Schedule           $schedule
     *
     * @return ResponseFactory|Response
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

        $schedule->refresh();

        // Update the schedule in BroadSign to reflect the new status
        UpdateBroadSignScheduleStatus::dispatch($schedule->id);

        return new Response($schedule->load("content", "owner:id,name"), 201);
    }
}
