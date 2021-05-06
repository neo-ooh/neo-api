<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsScreenshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts_screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId("burst_id")->constrained("contracts_bursts")->cascadeOnUpdate()->restrictOnDelete();
            $table->boolean("is_locked")->default(false);
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
        Schema::dropIfExists('contracts_screenshots');
    }
}
