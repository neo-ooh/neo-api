<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHeadlinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("headlines", function (Blueprint $table) {
            $table->id();
            $table->foreignId("actor_id")->nullable()->constrained("actors")->nullOnDelete()->cascadeOnUpdate();
            $table->string("style", 16);
            $table->timestamp("end_date")->nullable(true)->index();
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
        Schema::drop("headlines");
    }
}
