<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_06_02_162641_add_product_handling_creative.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Enums\ProductsFillStrategy;

return new class extends Migration {
    public function up() {
        Schema::table('products_categories', function (Blueprint $table) {
            $table->enum("fill_strategy", ProductsFillStrategy::getValues())
                  ->default(ProductsFillStrategy::digital)
                  ->change();
        });
    }
};
