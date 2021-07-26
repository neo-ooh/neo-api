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
        Schema::table("properties_traffic", function (Blueprint $table) {
            $table->unsignedBigInteger("traffic")->nullable()->change();
            $table->unsignedBigInteger("temporary")->nullable()->after("traffic");
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
