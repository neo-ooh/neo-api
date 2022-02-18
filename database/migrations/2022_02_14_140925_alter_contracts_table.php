<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_14_140925_alter_contracts_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterContractsTable extends Migration {
    public function up() {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId("external_id")->after("contract_id")->nullable();
            $table->renameColumn("owner_id", "salesperson_id");
            $table->foreignId("advertiser_id")->nullable()->constrained("advertisers", "id")->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId("client_id")->nullable()->change();
        });
    }

    public function down() {
        Schema::table('contracts', function (Blueprint $table) {
            //
        });
    }
}
