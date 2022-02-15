<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_14_142527_create_contract_lines_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractLinesTable extends Migration {
    public function up() {
        Schema::create('contracts_lines', function (Blueprint $table) {
            $table->foreignId("product_id")->constrained("products", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("flight_id")->constrained("contracts_flights", "id")->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId("external_id")->nullable();
            $table->unsignedInteger("spots");
            $table->unsignedDouble("media_value");
            $table->double("discount")->default(0);
            $table->set("discount_type", ["relative", "absolute", "cpm"])->default("relative");
            $table->unsignedDouble("price");
            $table->unsignedBigInteger("traffic");
            $table->unsignedBigInteger("impressions");

            $table->timestamps();
        });

        Schema::table('contracts_lines', function (Blueprint $table) {
            $table->primary(["product_id", "flight_id"]);
        });
    }

    public function down() {
        Schema::dropIfExists('contracts_lines');
    }
}
