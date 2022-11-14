<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_14_105356_add_schedules_batch_id.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSchedulesBatchId extends Migration {
    public function up() {
        Schema::table('schedules', function (Blueprint $table) {
            $table->uuid("batch_id")->after("locked_at")->nullable();
        });
    }
}
