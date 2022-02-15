<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_14_142330_create_contract_flights_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractFlightsTable extends Migration {
    public function up() {
        Schema::create('contracts_flights', function (Blueprint $table) {
            $table->id();

            $table->foreignId("contract_id")->constrained("contracts", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("name")->nullable();
            $table->timestamp("start_date");
            $table->timestamp("end_date");
            $table->set("type", ["guaranteed", "bonus", "bua"]);

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('contracts_flights');
    }
}
