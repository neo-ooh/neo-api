<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_21_140758_library_advertiser.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LibraryAdvertiser extends Migration {
    public function up() {
        Schema::table('libraries', function (Blueprint $table) {
            $table->foreignId("advertiser_id")
                  ->nullable()
                  ->after("owner_id")
                  ->constrained("advertisers", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }
}
