<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2020_10_07_201410_create_campaign_shares_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignSharesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('campaign_shares',
            function (Blueprint $table) {
                $table->foreignId("campaign_id")->constrained("campaigns")->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId("actor_id")->constrained("actors")->cascadeOnUpdate()->cascadeOnDelete();
                $table->timestamps();

                $table->primary(["campaign_id", "actor_id"]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists('campaign_shares');
    }
}
