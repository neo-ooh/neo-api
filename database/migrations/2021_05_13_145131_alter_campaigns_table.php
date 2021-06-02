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
        Schema::table("campaigns", function (Blueprint $table) {
            $table->renameColumn("broadsign_reservation_id", "external_id");
            $table->foreignId("network_id")->nullable()->after("id")->index()->constrained("networks")->cascadeOnUpdate()->cascadeOnDelete();
            // The network id column accept null value because this migration is happening on a DDB with already defined campaigns.
            // The column should be set back to `NOT NULL` once the networks/connection feature is live.
        });

        Schema::table("campaigns", function (Blueprint $table) {
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