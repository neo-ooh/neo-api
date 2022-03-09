<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_03_09_113340_add_contract_metadata_columns.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContractMetadataColumns extends Migration {
    public function up() {
        Schema::table('contracts', function (Blueprint $table) {
            $table->timestamp("start_date")->after("advertiser_id")->nullable();
            $table->timestamp("end_date")->after("start_date")->nullable();
            $table->unsignedBigInteger("expected_impressions")->default(0)->after("end_date");
            $table->unsignedBigInteger("received_impressions")->default(0)->after("expected_impressions");
        });
    }

    public function down() {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(["start_date", "end_date", "expected_impressions", "received_impressions"]);
        });
    }
}
