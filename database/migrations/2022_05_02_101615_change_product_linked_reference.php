<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_05_02_101615_change_product_linked_reference.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // First, we want to change linked products relation from using their external Ids to using their connect's ids.
        DB::update("UPDATE `products` AS `p1`
LEFT JOIN `products` AS `p2` ON `p1`.`external_linked_id` = `p2`.`external_id`
SET `p1`.`external_linked_id` = `p2`.`id`
WHERE `p1`.`external_linked_id` IS NOT NULL;
");

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn("external_linked_id", "linked_product_id");
            $table->foreign("linked_product_id", "product_linked_product_id_foreign")
                  ->on("products")
                  ->references("id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });
    }

    public function down() {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign("product_linked_product_id_foreign");
            $table->renameColumn("linked_product_id", "external_linked_id");
        });

        DB::update("UPDATE `products` AS `p1`
            JOIN `products` AS `p2` ON `p1`.`external_linked_id` = `p2`.`external_id`
            SET `p1`.`external_linked_id` = `p2`.`external_id`
            WHERE `p1`.`external_linked_id` IS NOT NULL;
        ");
    }
};
