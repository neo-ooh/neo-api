<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_13_111610_schedules_table_v2.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Add new columns to the table
        Schema::table('schedules', function (Blueprint $table) {
            // Add time columns
            $table->time("start_time")->after("start_date")->default("00:00:00");
            $table->time("end_time")->after("end_date")->default("23:59:00");

            // Add day of week column
            $table->unsignedTinyInteger("broadcast_days")->after("end_date")->default(127);

            // Remove is_locked column in favor of new `schedule_details` view
            $table->dropColumn("is_approved");
        });
    }
};
