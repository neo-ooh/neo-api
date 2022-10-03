<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_09_16_162116_add_schedule_locked_at.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleLockedAt extends Migration {
    public function up() {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dateTime("locked_at")->nullable()->after("is_locked");
        });
    }
}
