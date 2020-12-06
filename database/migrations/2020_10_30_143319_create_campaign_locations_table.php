<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_10_30_143319_create_campaign_locations_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::create('campaign_locations', function (Blueprint $table) {
            $table->foreignId("campaign_id")->constrained("campaigns")->cascadeOnDelete();
            $table->foreignId("location_id")->constrained("locations")->cascadeOnDelete();
            $table->unsignedInteger("broadsign_reservation_id")->nullable()->default(null);
            $table->timestamps();

            $table->primary([ "campaign_id", "location_id" ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::dropIfExists('campaign_locations');
    }
}
