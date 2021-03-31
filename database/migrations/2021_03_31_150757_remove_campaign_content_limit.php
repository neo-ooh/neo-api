<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCampaignContentLimit extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::dropColumns("campaigns", ["content_limit"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table("campaigns", function (Blueprint $table) {
            $table->unsignedInteger("content_limit")->after("display_duration");
        });
    }
}
