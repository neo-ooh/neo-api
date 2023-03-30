<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_30_104229_add_pricing_unit_product.php
 */

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `pricelists_products_categories` MODIFY COLUMN `pricing` enum('cpm','unit','unit-product')");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `pricelists_products` MODIFY COLUMN `pricing` enum('cpm','unit','unit-product')");
    }
};
