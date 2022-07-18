<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_13_150921_campaigns_table_v2.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table("campaigns", static function (Blueprint $table) {
            $table->renameColumn("owner_id", "parent_id");
        });

        Schema::table('campaigns', static function (Blueprint $table) {
            $table->foreignId("creator_id")->nullable()->constrained("actors", "id")->cascadeOnUpdate()->nullOnDelete();

            $table->time("start_time")->after("start_date")->default("00:00:00");
            $table->time("end_time")->after("end_date")->default("23:59:00");

            $table->unsignedTinyInteger("broadcast_days")->after("end_time")->default(127);

            $table->renameColumn("loop_saturation", "occurrences_in_loop");

            $table->dropConstrainedForeignId("format_id");
            $table->dropColumn("external_id");
            $table->dropColumn("network_id");
        });

        Schema::table('campaigns', static function (Blueprint $table) {
            $table->float("occurrences_in_loop")->default(1)->change();
        });
    }
};
