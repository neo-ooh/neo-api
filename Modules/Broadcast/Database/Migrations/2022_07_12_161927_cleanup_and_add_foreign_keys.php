<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_12_161927_cleanup_and_add_foreign_keys.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;

return new class extends Migration {
    public function up(): void {
        // For each of the new broadcast resources (contents, schedules, campaigns), we remove foreign keys on old id columns, old id columns, and add new FK with the new id columns

        $output = new ConsoleOutput();
        $output->writeln("Dropping foreign keys and columns, and renaming references columns...");

        // Creatives
        Schema::table("creatives", static function (Blueprint $table) {
            $table->dropConstrainedForeignId("content_id");
            $table->renameColumn("content_id_tmp", "content_id");
        });

        // Schedules
        Schema::table("schedules", static function (Blueprint $table) {
            $table->dropConstrainedForeignId("content_id");
            $table->renameColumn("content_id_tmp", "content_id");
            $table->dropConstrainedForeignId("campaign_id");
            $table->renameColumn("campaign_id_tmp", "campaign_id");
        });

        // Schedule Reviews
        Schema::table("schedule_reviews", static function (Blueprint $table) {
            $table->dropForeign("reviews_schedule_id_foreign");
            $table->dropColumn("schedule_id");
            $table->renameColumn("schedule_id_tmp", "schedule_id");
        });

        // Campaign Shares
        Schema::drop("campaign_shares");
        Schema::create("campaign_shares", static function (Blueprint $table) {
            $table->foreignId("campaign_id");
            $table->foreignId("actor_id")->constrained("actors", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(["campaign_id", "actor_id"]);
        });

        // Campaign Locations
        Schema::table("campaign_locations", static function (Blueprint $table) {
            $table->dropForeign("campaign_locations_campaign_id_foreign");
            $table->dropPrimary("PRIMARY");
            $table->dropColumn("campaign_id");
            $table->renameColumn("campaign_id_tmp", "campaign_id");
            $table->primary(["campaign_id", "location_id"]);
        });

        $output->writeln("Switching primary columns...");

        // Contents
        Schema::table("contents", static function (Blueprint $table) {
            $table->dropColumn("id");
            $table->renameColumn("id_tmp", "id");
            $table->primary("id");
        });

        // Schedules
        Schema::table("schedules", static function (Blueprint $table) {
            $table->dropColumn("id");
            $table->renameColumn("id_tmp", "id");
            $table->primary("id");
        });

        // Campaigns
        Schema::table("campaigns", static function (Blueprint $table) {
            $table->dropColumn("id");
            $table->renameColumn("id_tmp", "id");
            $table->primary("id");
        });

        $output->writeln("Rebuilding foreign keys...");

        // Creatives
        Schema::table("creatives", static function (Blueprint $table) {
            $table->foreign("content_id")->references("id")->on("contents")->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::table("contents", static function (Blueprint $table) {
            $table->foreign("id")->references("id")->on("broadcast_resources")->cascadeOnUpdate()->restrictOnDelete();
        });

        // Schedules
        Schema::table("schedules", static function (Blueprint $table) {
            $table->foreign("id")->references("id")->on("broadcast_resources")->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign("content_id")->references("id")->on("contents")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign("campaign_id")->references("id")->on("campaigns")->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::table("campaigns", static function (Blueprint $table) {
            $table->foreign("id")->references("id")->on("broadcast_resources")->cascadeOnUpdate()->restrictOnDelete();
        });

        // Schedule Reviews
        Schema::table("schedule_reviews", static function (Blueprint $table) {
            $table->foreign("schedule_id")->references("id")->on("schedules")->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Campaign Shares
        Schema::table("campaign_shares", static function (Blueprint $table) {
            $table->foreign("campaign_id")->references("id")->on("campaigns")->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Campaign Locations
        Schema::table("campaign_locations", static function (Blueprint $table) {
            $table->foreign("campaign_id")->references("id")->on("campaigns")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }
};
