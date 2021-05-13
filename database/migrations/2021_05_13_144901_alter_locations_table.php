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
        Schema::table("locations", function (Blueprint $table) {
            $table->renameColumn("broadsign_display_unit", "external_id");
            $table->foreignId("network_id")->after("id")->index()->constrained("networks")->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::table("locations", function (Blueprint $table) {
            $table->text("external_id")->change();
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
