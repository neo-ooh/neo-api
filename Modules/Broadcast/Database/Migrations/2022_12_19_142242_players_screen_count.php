<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_12_19_142242_players_screen_count.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlayersScreenCount extends Migration {
    public function up() {
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedInteger("screen_count")->default(0)->after("name");
        });
    }
}
