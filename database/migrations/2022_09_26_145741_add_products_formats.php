<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_09_26_145741_add_products_formats.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductsFormats extends Migration {
    public function up() {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId("format_id")->nullable()->after("name_fr")->constrained("formats", "id");
        });
    }
}
