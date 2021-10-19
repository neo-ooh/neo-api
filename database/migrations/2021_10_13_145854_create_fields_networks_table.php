<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_10_13_145854_create_fields_networks_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldsNetworksTable extends Migration {
    public function up() {
        Schema::create('fields_networks', function (Blueprint $table) {
            $table->foreignId("field_id")->constrained("fields")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("network_id")->constrained("networks")->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger("order");
        });
    }

    public function down() {
        Schema::dropIfExists('fields_networks');
    }
}
