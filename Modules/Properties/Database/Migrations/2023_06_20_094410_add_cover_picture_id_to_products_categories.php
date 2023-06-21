<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_06_20_094410_add_cover_picture_id_to_products_categories.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products_categories', function (Blueprint $table) {
            $table->foreignId("cover_picture_id")
                  ->after("screen_type_id")
                  ->nullable()
                  ->constrained("properties_pictures", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }
};
