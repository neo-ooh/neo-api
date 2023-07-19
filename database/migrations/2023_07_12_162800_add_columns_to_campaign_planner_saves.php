<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_12_162800_add_columns_to_campaign_planner_saves.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('campaign_planner_saves', function (Blueprint $table) {
            $table->string("uid", 64)->after("id");
            $table->string("version", 16)->after("actor_id");
            $table->string("contract", 32)->after("version")->nullable()->default(null);
            $table->string("client_name", 128)->after("contract")->nullable()->default(null);
            $table->string("advertiser_name", 128)->after("client_name")->nullable()->default(null);
        });
    }
};
