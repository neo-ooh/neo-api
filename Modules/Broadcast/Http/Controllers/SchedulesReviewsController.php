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
use Neo\Http\Controllers\Controller;
use Neo\Modules\Broadcast\Http\Requests\Reviews\StoreReviewRequest;
use Neo\Modules\Broadcast\Models\Schedule;
use Neo\Modules\Broadcast\Models\ScheduleReview;

class SchedulesReviewsController extends Controller {
    /**
     * @param StoreReviewRequest $request
     * @param Schedule           $schedule
     *
     * @return Response
     */
    public function store(StoreReviewRequest $request, Schedule $schedule): Response {
        $review              = new ScheduleReview();
        $review->schedule_id = $schedule->id;
        $review->reviewer_id = Auth::id();
        $review->approved    = $request->input("approved");
        $review->message     = $request->input("message");
        $review->save();

        $schedule->promote();

        return new Response($review->load("reviewer"), 201);
    }
}
