<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("inventory", function (Blueprint $table) {
            $table->foreignId("location_id")->after("year");
            $table->string("name", 128)->after("max_booking");
            $table->date("start_date")->after("name");
            $table->date("end_date")->after("start_date");
        });

        // Add an index on the locations' display unit id for access performances
        Schema::table("locations", function (Blueprint $table) {
            $table->index("broadsign_display_unit");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns("inventory", ["location_id", "name", "start_date", "end_date"]);
        Schema::table("locations", function (Blueprint $table) {
            $table->dropIndex(["broadsign_display_unit"]);
        });
    }
}
