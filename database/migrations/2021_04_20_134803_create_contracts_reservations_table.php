<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId("contract_id")->constrained("contracts")->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedBigInteger("broadsign_reservation_id");
            $table->string("network", 16);
            $table->string("name", 256);
            $table->string("original_name", 256);
            $table->timestamp("start_date");
            $table->timestamp("end_date");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts_reservations');
    }
}
