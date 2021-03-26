<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHeadlinesMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("headlines_messages", function (Blueprint $table) {
            $table->id();
            $table->foreignId("headline_id")->constrained("headlines")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("locale", 5)->nullable(false);
            $table->text("message");
            $table->timestamps();

            $table->unique(["headline_id", "locale"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop("headlines_messages");
    }
}
