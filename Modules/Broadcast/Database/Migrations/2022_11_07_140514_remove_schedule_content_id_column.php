<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_07_140514_remove_schedule_content_id_column.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveScheduleContentIdColumn extends Migration {
    public function up() {
        Schema::table('schedules', static function (Blueprint $table) {
            $table->dropConstrainedForeignId("content_id");
        });
    }
}
