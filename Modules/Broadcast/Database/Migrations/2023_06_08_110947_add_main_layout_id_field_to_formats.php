<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_06_08_110947_add_main_layout_id_field_to_formats.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('formats', function (Blueprint $table) {
            $table->foreignId("main_layout_id")
                  ->after("content_length")
                  ->nullable()
                  ->constrained("layouts", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }
};
