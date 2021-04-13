<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDynamicCreativesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynamic_creatives', function (Blueprint $table) {
            $table->foreignId("creative_id")->primary()->constrained("creatives")->cascadeOnDelete()->cascadeOnDelete();
            $table->text("url");
            $table->unsignedInteger("refresh_interval")->default(0);
            $table->text("thumbnail_path");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dynamic_creatives');
    }
}
