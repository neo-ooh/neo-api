<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_06_02_111606_add_product_categories_props.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products_categories', function (Blueprint $table) {
            $table->boolean("allows_motion")->after("allows_audio")->default(true);
            $table->unsignedDouble("screen_size_in")->nullable()->after("production_cost");
            $table->foreignId("screen_type_id")
                  ->nullable()
                  ->after("screen_size_in")
                  ->constrained("screen_types", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }
};
