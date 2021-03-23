<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_03_11_145503_create_access_tokens_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string("name", 64);
            $table->string("token", 256);
            $table->timestamps();
        });

        Schema::create('access_tokens_capabilities', function (Blueprint $table) {
            $table->foreignId("access_token_id")->constrained("access_tokens")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("capability_id")->constrained("capabilities")->cascadeOnUpdate()->cascadeOnDelete();
            $table->text("value");
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
        Schema::dropIfExists('access_tokens');
    }
}
