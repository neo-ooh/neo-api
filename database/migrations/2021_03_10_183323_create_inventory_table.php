<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->unsignedBigInteger("skin_id");
            $table->unsignedInteger("year");
            $table->json("bookings");
            $table->unsignedInteger("max_booking");
            $table->timestamps();
        });

        Schema::table('inventory', function (Blueprint $table) {
           $table->primary(["skin_id", "year"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory');
    }
}
