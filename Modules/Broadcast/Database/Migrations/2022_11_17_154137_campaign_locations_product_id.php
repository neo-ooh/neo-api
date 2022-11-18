<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_17_154137_campaign_locations_product_id.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CampaignLocationsProductId extends Migration {
    public function up() {
        Schema::table('campaign_locations', function (Blueprint $table) {
            $table->foreignId("product_id")
                  ->nullable()
                  ->after("format_id")
                  ->constrained("products", "id")
                  ->nullOnDelete()
                  ->cascadeOnUpdate();
        });
    }
}
