<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_03_30_151447_add_products_media_types.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('products_categories', function (Blueprint $table) {
            $table->string("allowed_media_types", 128)->after("format_id")->default("image,video");
            $table->boolean("allows_audio")->after("allowed_media_types")->default(false);

            $table->dropColumn("external_id");
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string("allowed_media_types", 128)->after("linked_product_id")->default("");
            $table->boolean("allows_audio")->after("allowed_media_types")->nullable();
        });
    }
};
