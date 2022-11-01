<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_10_26_161722_broadcaster_connection_contracts_flag.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BroadcasterConnectionContractsFlag extends Migration {
    public function up() {
        Schema::table('broadcasters_connections', function (Blueprint $table) {
            $table->tinyInteger("contracts")->default(0)->after("active");
        });

        DB::table("parameters")->where("slug", "=", "CONTRACTS_CONNECTION")->delete();
    }
}
