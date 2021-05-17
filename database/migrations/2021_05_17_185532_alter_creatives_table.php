<?php

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
        Schema::table("creatives", function (Blueprint $table) {
            $table->renameColumn("broadsign_ad_copy_id", "external_id_broadsign");
            $table->unsignedBigInteger("external_id_pisignage")->unique()->nullable(true)->after("broadsign_ad_copy_id");
        });

        Schema::table("creatives", function (Blueprint $table) {
            $table->unsignedBigInteger("external_id_broadsign")->change();
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
