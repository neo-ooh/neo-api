<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_28_152627_fixups.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Fix disparities and inconsistencies in the DB structure
        Schema::table('actors', static function (Blueprint $table) {
            $table->dropForeign("actors_locked_by_foreign");
            $table->foreign("locked_by")->references("id")->on("actors")->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::table("clients", static function (Blueprint $table) {
            $table->dropColumn("broadsign_customer_id");
            $table->renameColumn("external_id", "odoo_id");
        });

        Schema::table("advertisers", static function (Blueprint $table) {
            $table->renameColumn("external_id", "odoo_id");
        });

        Schema::table("contracts_reservations", static function (Blueprint $table) {
            $table->dropConstrainedForeignId("network_id");
        });

        Schema::rename("formats_display_types", "format_display_types");
        Schema::rename("headlines_messages", "headline_messages");

        Schema::table("properties_pictures", static function (Blueprint $table) {
            $table->dropColumn(["extension"]);
        });

        Schema::table("properties_traffic", static function (Blueprint $table) {
            $table->unsignedSmallInteger("week")->change();
        });

        Schema::table("property_traffic_source", static function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }
};
