<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create("creatives_external_ids", function (Blueprint $table) {
            $table->foreignId("creative_id")->constrained("creatives");
            $table->foreignId("network_id")->constrained("networks");
            $table->text("external_id");
            $table->timestamps();

            $table->primary(["creative_id", "network_id"]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
};
