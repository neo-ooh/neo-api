<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_09_26_145544_add_products_categories_formats.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductsCategoriesFormats extends Migration {
    public function up() {
        Schema::table('products_categories', function (Blueprint $table) {
            $table->foreignId("format_id")->nullable()->after("type")->constrained("formats", "id");
        });
    }
}
