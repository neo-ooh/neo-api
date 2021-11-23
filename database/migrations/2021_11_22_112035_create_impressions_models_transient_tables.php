<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_11_22_112035_create_impressions_models_transient_tables.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImpressionsModelsTransientTables extends Migration {
    public function up() {
        Schema::create('products_categories_impressions_models', function (Blueprint $table) {
            $table->unsignedBigInteger("product_category_id");
            $table->foreign("product_category_id", "products_categories_impressions_models_foreign")
                  ->on("products_categories")
                  ->references("id")->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedBigInteger("impressions_model_id");
            $table->foreign("impressions_model_id", "impressions_models_products_categories_foreign")
                  ->on("impressions_models")
                  ->references("id")->cascadeOnDelete()->cascadeOnUpdate();
        });

        Schema::create('products_impressions_models', function (Blueprint $table) {
            $table->unsignedBigInteger("product_id");
            $table->foreign("product_id", "products_impressions_models_foreign")
                  ->on("products")
                  ->references("id");

            $table->unsignedBigInteger("impressions_model_id");
            $table->foreign("impressions_model_id", "impressions_models_products_foreign")
                  ->on("impressions_models")
                  ->references("id");
        });
    }

    public function down() {
        Schema::dropIfExists('products_categories_impressions_models');
        Schema::dropIfExists('products_impressions_models');
    }
}
