<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_09_01_103945_add_network_id_to_properties_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNetworkIdToPropertiesTable extends Migration {
    public function up() {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId("network_id")->after("address_id")->nullable(true)->constrained("networks", "id")->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down() {
        Schema::table('properties', function (Blueprint $table) {
            //
        });
    }
}
