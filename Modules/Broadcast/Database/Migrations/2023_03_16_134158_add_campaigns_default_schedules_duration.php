<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_16_134158_add_campaigns_default_schedules_duration.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->unsignedInteger("default_schedule_duration_days")->after("broadcast_days")->default(0);
        });

        DB::table("campaigns")
          ->whereNull("flight_id")
          ->update([
                       "default_schedule_duration_days" => 14,
                   ]);
    }
};
