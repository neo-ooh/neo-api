<?php

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
            $table->foreignId("trigger_id")->after("is_fullscreen")->constrained("broadsign_triggers")->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId("separation_id")->after("trigger_id")->constrained("broadsign_separations")->cascadeOnUpdate()->restrictOnDelete();
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
