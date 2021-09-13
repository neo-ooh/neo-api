<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_13_141134_create_campaign_planner_saves_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignPlannerSavesTable extends Migration {
    public function up() {
        Schema::create('campaign_planner_saves', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string("name", 64);
            $table->foreignId("actor_id")->constrained("actors")->cascadeOnDelete()->cascadeOnUpdate();
            $table->json("data");

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('campaign_planner_saves');
    }
}
