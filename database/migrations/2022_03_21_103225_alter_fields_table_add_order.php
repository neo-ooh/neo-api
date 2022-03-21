<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_03_21_103225_alter_fields_table_add_order.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('fields', function (Blueprint $table) {
            $table->unsignedInteger("order")->default(0)->after("category_id");
        });
    }

    public function down() {
        Schema::table('fields', function (Blueprint $table) {
            $table->dropColumn(["order"]);
        });
    }
};
