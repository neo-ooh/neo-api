<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_23_163629_add_campaign_flight_id.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampaignFlightId extends Migration {
    public function up() {
        Schema::table('campaigns', static function (Blueprint $table) {
            $table->foreignId("flight_id")
                  ->nullable()
                  ->after("creator_id")
                  ->constrained("contracts_flights", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }
}
