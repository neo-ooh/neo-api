<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_08_04_151806_alter_addresses_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterAddressesTable extends Migration {
    public function up() {
        Schema::table('addresses', function (Blueprint $table) {
            $table->point("geolocation", "4326")->nullable();
        });
    }

    public function down() {
        Schema::table('addresses', function (Blueprint $table) {
            $table->removeColumn("geolocation");
        });
    }
}
