<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_04_25_111642_add_brands_fulltext_search.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('brands', function (Blueprint $table) {
            $table->fullText(["name_en", "name_fr"]);
        });
    }

    public function down() {
        Schema::table('brands', function (Blueprint $table) {
        });
    }
};
