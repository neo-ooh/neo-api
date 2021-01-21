<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBroadsignStoredIds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // remove the broadsign ids in the contents table
        Schema::table("contents", function(Blueprint $table) {
            $table->removeColumn('broadsign_content_id');
            $table->removeColumn('broadsign_bundle_id');
        });

        // And add the broadisn bundle id in the schedule table
        Schema::table("schedules", function(Blueprint $table) {
            $table->unsignedBigInteger("broadsign_bundle_id")->nullable()->default(null)->after('owner_id');
        });

        // A bundle links one or multiple ad-copies to a schedule. A bundle seems to be specific and needs to be created
        // for each and every schedule of a content.
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("contents", function(Blueprint $table) {
            $table->unsignedInteger("broadsign_content_id")->nullable()->default(null);
            $table->unsignedInteger("broadsign_bundle_id")->nullable()->default(null);
        });
    }
}
