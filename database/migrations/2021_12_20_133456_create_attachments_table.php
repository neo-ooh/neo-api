<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_12_20_133456_create_attachments_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachmentsTable extends Migration {
    public function up() {
        Schema::create('attachments', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string("locale", 5);

            $table->string("name", 64);
            $table->string("filename", 128);

            $table->timestamps();
        });

        Schema::create("products_attachments", static function (Blueprint $table) {
            $table->foreignId("product_id")->constrained("products")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("attachment_id")->constrained("attachments")->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::create("products_categories_attachments", static function (Blueprint $table) {
            $table->foreignId("product_category_id")->constrained("products_categories")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("attachment_id")->constrained("attachments")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('products_attachments');
        Schema::dropIfExists('products_categories_attachments');
    }
}
