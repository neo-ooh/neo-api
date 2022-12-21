<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_23_164416_add_contracts_flights_uid.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use function Ramsey\Uuid\v4;

class AddContractsFlightsUid extends Migration {
    public function up() {
        Schema::table('contracts_flights', static function (Blueprint $table) {
            $table->string("uid", 36)->after("id");
        });

        $flights = DB::table("contracts_flights")->get();

        foreach ($flights as $flight) {
            DB::table("contracts_flights")
              ->where("id", "=", $flight->id)
              ->update([
                           "uid" => v4(),
                       ]);
        }
    }
}
