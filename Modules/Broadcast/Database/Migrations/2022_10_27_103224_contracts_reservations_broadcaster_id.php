<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_10_27_103224_contracts_reservations_broadcaster_id.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ContractsReservationsBroadcasterId extends Migration {
    public function up() {
        Schema::table('contracts_reservations', function (Blueprint $table) {
            $table->foreignId("broadcaster_id")
                  ->after("flight_id")
                  ->nullable()
                  ->constrained("broadcasters_connections", "id")
                  ->cascadeOnUpdate()
                  ->cascadeOnDelete();
            $table->string("external_id", 64)->change();
        });

        \Illuminate\Support\Facades\DB::table("contracts_reservations")
                                      ->update(["broadcaster_id" => 1]);

        Schema::table('contracts_reservations', function (Blueprint $table) {
            $table->foreignId("broadcaster_id")
                  ->nullable(false)->change();
        });
    }
}
