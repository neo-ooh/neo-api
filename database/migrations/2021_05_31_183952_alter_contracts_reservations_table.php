<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table("contracts_reservations", function (Blueprint $table) {
            $table->renameColumn("broadsign_reservation_id", "external_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table("contracts_reservations", function (Blueprint $table) {
            $table->renameColumn("external_id", "broadsign_reservation_id");
        });
    }
};
