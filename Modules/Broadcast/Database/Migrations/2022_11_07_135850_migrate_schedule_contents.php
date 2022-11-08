<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_07_135850_migrate_schedule_contents.php
 */

use Illuminate\Database\Migrations\Migration;

class MigrateScheduleContents extends Migration {
    public function up() {
        $schedules = DB::table("schedules")->orderBy("id")->lazy(500);

        foreach ($schedules as $schedule) {
            \Illuminate\Support\Facades\DB::table("schedule_contents")
                                          ->insert([
                                              "schedule_id" => $schedule->id,
                                              "content_id"  => $schedule->content_id,
                                              "created_at"  => $schedule->created_at,
                                              "updated_at"  => $schedule->created_at,
                                          ]);
        }
    }
}
