<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_08_31_120848_add_network_color_to_networks_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNetworkColorToNetworksTable extends Migration {
    public function up() {
        Schema::table('networks', function (Blueprint $table) {
            $table->string("color", 6)->default("000000")->after("name");
        });
    }

    public function down() {
        Schema::table('networks', function (Blueprint $table) {
            //
        });
    }
}
