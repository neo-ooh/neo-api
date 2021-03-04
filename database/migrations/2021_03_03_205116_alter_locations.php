<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Neo\Models\DisplayType;
use Neo\Models\Location;

class AlterLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("locations", function (Blueprint $table) {
            $table->foreignId("display_type_id")->after("broadsign_display_unit");
        });

        $locations = Location::all();
        foreach ($locations as $location) {
            $location->display_type_id = $location->format->display_types->first()->id;
            $location->save();
        }

        Schema::table("locations", function (Blueprint $table) {
            $table->dropForeign(['format_id']);
            $table->dropColumn('format_id');
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
}
