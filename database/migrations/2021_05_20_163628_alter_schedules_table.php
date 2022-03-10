<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_05_20_163628_alter_schedules_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("schedules", function (Blueprint $table) {
            $table->renameColumn("broadsign_bundle_id", "external_id_1");
            $table->renameColumn("broadsign_schedule_id", "external_id_2");
        });

        Schema::table("schedules", function (Blueprint $table) {
            $table->text("external_id_1")->change();
            $table->text("external_id_2")->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
