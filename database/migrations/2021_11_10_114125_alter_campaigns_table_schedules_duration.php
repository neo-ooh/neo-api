<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_10_114125_alter_campaigns_table_schedules_duration.php
 */

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\Campaign;

class AlterCampaignsTableSchedulesDuration extends Migration {
    public function up() {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->renameColumn("display_duration", "schedules_default_length");
            $table->unsignedDouble("schedules_max_length")->nullable(false)->after("display_duration");
        });

        /** @var Collection $campaigns */
        $campaigns = Campaign::all(["id", "schedules_default_length"]);
        foreach ($campaigns as $campaign) {
            $campaign->schedules_max_length = $campaign->schedules_default_length;
        }

        $campaigns->each->save();
    }

    public function down() {
        Schema::table('campaigns', function (Blueprint $table) {
            //
        });
    }
}
