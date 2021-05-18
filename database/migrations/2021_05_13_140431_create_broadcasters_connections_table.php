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
        Schema::create('broadcasters_connections', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->index()->default(DB::raw('(UUID())'));
            $table->set("broadcaster", ["broadsign", "pisignage"]);
            $table->string("name", 64);
            $table->boolean("active")->default("1");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('broadcasters_connections');
    }
};
