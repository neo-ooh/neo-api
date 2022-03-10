<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_18_110925_alter_contracts_table_followup.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterContractsTableFollowup extends Migration {
    public function up() {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn(["start_date", "end_date", "data", "advertiser_name", "executive_name", "presented_to"]);
        });
    }

    public function down() {
        Schema::table('contracts', function (Blueprint $table) {
            //
        });
    }
}
