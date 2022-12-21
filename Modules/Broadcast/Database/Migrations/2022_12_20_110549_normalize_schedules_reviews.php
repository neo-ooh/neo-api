<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_12_20_110549_normalize_schedules_reviews.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Neo\Modules\Broadcast\Models\ScheduleReview;

class NormalizeSchedulesReviews extends Migration {
    public function up(): void {
        // This is a one-of script to make sure all schedules at the time of the migration are accepted as the changes
        // made to the schedules validation structure surfaced inconsistencies with some old schedules.
        // Load all schedules who are not approved, nor rejected or deleted
        $schedules = DB::table("schedules")
                       ->join("schedule_details", "schedules.id", "=", "schedule_details.schedule_id")
                       ->where("schedules.is_locked", "=", true)
                       ->where("schedule_details.is_approved", "=", false)
                       ->where("schedule_details.is_rejected", "=", false)
                       ->whereNull("schedules.deleted_at")
                       ->get();

        foreach ($schedules as $schedule) {
            $review              = new ScheduleReview();
            $review->reviewer_id = $schedule->owner_id;
            $review->schedule_id = $schedule->id;
            $review->approved    = true;
            $review->message     = "[auto-approved]";
            $review->save();
        }
    }
}
