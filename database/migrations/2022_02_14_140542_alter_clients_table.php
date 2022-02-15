<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_14_140542_alter_clients_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterClientsTable extends Migration {
    public function up() {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId("external_id")->after("id")->nullable();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(["external_id"]);
            $table->dropSoftDeletes();
        });
    }
}
