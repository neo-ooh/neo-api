<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_13_102548_contents_table_v2.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn("broadsign_content_id");
            $table->dropColumn("broadsign_bundle_id");

            $table->renameColumn("scheduling_duration", "max_schedule_duration");
            $table->renameColumn("scheduling_times", "max_schedule_count");
        });
    }
};
