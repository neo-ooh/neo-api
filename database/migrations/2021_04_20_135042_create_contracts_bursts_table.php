<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsBurstsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts_bursts', function (Blueprint $table) {
            $table->id();
            $table->foreignId("contract_id")->constrained("contracts")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("actor_id")->nullable()->constrained("actors")->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId("location_id")->nullable()->constrained("locations")->cascadeOnUpdate()->nullOnDelete();
            $table->timestamp("start_at");
            $table->string("status", 64);
            $table->tinyInteger("scale_percent");
            $table->unsignedInteger("duration_ms");
            $table->unsignedInteger("frequency_ms");
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
        Schema::dropIfExists('contracts_bursts');
    }
}
