<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_14_143905_alter_contracts_reservations_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Jobs\Contracts\MigrateContractsJob;

class AlterContractsReservationsTable extends Migration {
    public function up() {
        Schema::table('contracts_reservations', function (Blueprint $table) {
            $table->foreignId("flight_id")
                  ->after("contract_id")
                  ->nullable()
                  ->constrained("contracts_flights", "id")
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->dropColumn(["network"]);

            $table->foreignId("network_id")
                  ->nullable()
                  ->constrained("networks", "id")
                  ->cascadeOnUpdate()
                  ->nullOnDelete();
        });

        MigrateContractsJob::dispatchSync();
    }

    public function down() {
        Schema::table('contracts_reservations', function (Blueprint $table) {
            //
        });
    }
}