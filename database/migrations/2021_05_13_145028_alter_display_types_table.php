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
        Schema::table("display_types", function (Blueprint $table) {
            $table->renameColumn("broadsign_display_type_id", "external_id");
            $table->foreignId("connectin_id")->nullable()->after("id")->index()->constrained("broadcasters_connection")->cascadeOnUpdate()->cascadeOnDelete();
            // The network id column accept null value because this migration is happening on a DDB with already defined display types.
            // The column should be set back to `NOT NULL` once the networks/connection feature is live.
        });

        Schema::table("display_types", function (Blueprint $table) {
            $table->text("external_id")->nullable()->change();
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
