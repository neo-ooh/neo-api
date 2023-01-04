<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_21_105039_alter_formats_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('formats', function (Blueprint $table) {
            $table->string("slug", 16)->after("id");
            $table->foreignId("network_id")->after("slug");
        });

        DB::table("formats")->update(["network_id" => 1]);

        Schema::table("formats", function (Blueprint $table) {
            $table->foreign("network_id")->references("id")->on("networks")->cascadeOnUpdate()->restrictOnDelete();
        });

        Schema::create("format_loop_configurations", function (Blueprint $table) {
            $table->foreignId("format_id")->constrained("formats", "id")->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId("loop_configuration_id")
                  ->constrained("loop_configurations", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
        });
    }
};
