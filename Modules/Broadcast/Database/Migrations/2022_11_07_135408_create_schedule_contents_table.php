<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_07_135408_create_schedule_contents_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleContentsTable extends Migration {
    public function up() {
        Schema::create('schedule_contents', static function (Blueprint $table) {
            $table->id();

            $table->foreignId("schedule_id")->constrained("schedules", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("content_id")->constrained("contents", "id")->cascadeOnUpdate()->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('schedule_content_disabled_formats', static function (Blueprint $table) {
            $table->foreignId("schedule_content_id")
                  ->constrained("schedule_contents", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->foreignId("format_id")->constrained("formats", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
}
