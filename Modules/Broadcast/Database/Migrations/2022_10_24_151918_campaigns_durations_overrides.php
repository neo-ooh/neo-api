<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_10_24_151918_campaigns_durations_overrides.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CampaignsDurationsOverrides extends Migration {
    public function up() {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->renameColumn("schedules_default_length", "static_duration_override");
            $table->renameColumn("schedules_max_length", "dynamic_duration_override");
        });
    }
}
