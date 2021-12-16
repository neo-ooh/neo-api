<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_12_15_113428_add_libraries_hiddent_formats.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLibrariesHiddentFormats extends Migration {
    public function up() {
        Schema::table('libraries', function (Blueprint $table) {
            $table->json("hidden_formats")->default("[]")->after("content_limit");
        });
    }

    public function down() {
        Schema::table('libraries', function (Blueprint $table) {
            $table->dropColumn("hidden_formats");
        });
    }
}
