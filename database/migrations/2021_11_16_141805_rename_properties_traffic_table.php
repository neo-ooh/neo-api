<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_16_141805_rename_properties_traffic_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePropertiesTrafficTable extends Migration {
    public function up() {
        Schema::table('properties_traffic', function (Blueprint $table) {
            $table->dropForeign("properties_traffic_property_id_foreign");
            $table->rename("properties_traffic_monthly");
        });

        Schema::table('properties_traffic_monthly', function (Blueprint $table) {
            $table->foreign("property_id")->on("properties")->references("actor_id")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::table('properties_traffic_monthly', function (Blueprint $table) {
            $table->rename("properties_traffic");
        });
    }
}
