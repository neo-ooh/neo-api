<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_03_22_142748_alter_layouts_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterLayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("formats_layouts", function (Blueprint $table) {
            $table->foreignId("trigger_id")->after("is_fullscreen")->nullable(true)->constrained("broadsign_triggers")->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId("separation_id")->after("trigger_id")->nullable(true)->constrained("broadsign_separations")->cascadeOnUpdate()->restrictOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns("formats_layouts", ["trigger_id", "separation_id"]);
    }
}
