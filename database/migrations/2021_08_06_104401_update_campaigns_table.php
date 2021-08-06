<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_08_06_104401_update_campaigns_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCampaignsTable extends Migration {
    public function up() {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->unsignedInteger("priority")->default(0)->after("loop_saturation");
        });
    }

    public function down() {
    }
}
