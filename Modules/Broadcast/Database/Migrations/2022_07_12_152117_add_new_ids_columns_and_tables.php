<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_12_152117_add_new_ids_columns_and_tables.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Add `id_tmp` columns to the tables that are switching to `broadcast_resources` ids
        // and add additional `_tmp` columns to tables that are referencing tables who are switching to `broadcast_resources`
        Schema::table('contents', static function (Blueprint $table) {
            $table->foreignId("id_tmp")
                  ->after("id");
        });

        Schema::table('creatives', static function (Blueprint $table) {
            $table->foreignId("content_id_tmp")
                  ->after("content_id");
        });

        Schema::table('schedules', static function (Blueprint $table) {
            $table->foreignId("id_tmp")
                  ->after("id");
            $table->foreignId("campaign_id_tmp")
                  ->after("campaign_id");
            $table->foreignId("content_id_tmp")
                  ->after("content_id");
        });

        Schema::rename("reviews", "schedule_reviews");
        Schema::table('schedule_reviews', static function (Blueprint $table) {
            $table->foreignId("schedule_id_tmp")
                  ->after("schedule_id");
        });

        Schema::table('campaigns', static function (Blueprint $table) {
            $table->foreignId("id_tmp")
                  ->after("id");
        });

        Schema::table('campaign_shares', static function (Blueprint $table) {
            $table->foreignId("campaign_id_tmp")
                  ->after("campaign_id");
        });

        Schema::table('campaign_locations', static function (Blueprint $table) {
            $table->foreignId("campaign_id_tmp")
                  ->after("campaign_id");
        });
    }
};
