<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_02_24_192723_alter_schedules_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Schedule;

class AlterSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("schedules", function (Blueprint $table) {
            $table->boolean("is_approved")->default(0)->after("locked");
        });

        Schedule::all()->each(/**
         * @param Schedule $schedule
         */ function ($schedule) {
             $schedule->is_approved = $schedule->getOldIsApprovedAttribute();
             $schedule->save();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns("schedules", ["is_approved"]);
    }
}