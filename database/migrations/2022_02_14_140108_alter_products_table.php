<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_14_140108_alter_products_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProductsTable extends Migration {
    public function up() {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger("spot_length")->default(0)->after("external_linked_id");
            $table->unsignedInteger("spots_count")->default(0)->after("spot_length");
            $table->unsignedInteger("extra_spots")->default(0)->after("spots_count");

            $table->softDeletes();
        });
    }

    public function down() {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(["spot_length", "spots_count", "extra_spots"]);
            $table->dropSoftDeletes();
        });
    }
}
