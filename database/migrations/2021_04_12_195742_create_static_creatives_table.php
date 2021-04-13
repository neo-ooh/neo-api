<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaticCreativesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('static_creatives', function (Blueprint $table) {
            $table->foreignId("creative_id")->primary()->constrained("creatives")->cascadeOnDelete()->cascadeOnDelete();
            $table->string("extension", 8);
            $table->string("checksum", 64);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('static_creatives');
    }
}
