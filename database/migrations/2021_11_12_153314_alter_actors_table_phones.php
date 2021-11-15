<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_12_153314_alter_actors_table_phones.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterActorsTablePhones extends Migration {
    public function up() {
        Schema::table('actors', function (Blueprint $table) {
            $table->foreignId("phone_id")
                  ->nullable(true)
                  ->default(null)
                  ->constrained("phones")
                  ->cascadeOnUpdate()
                  ->nullOnDelete()
                  ->after("branding_id");
            $table->set("two_fa_method", ["email", "phone"])->default("email")->after("phone_id");
        });
    }

    public function down() {
        Schema::table('actors', function (Blueprint $table) {

        });
    }
}
