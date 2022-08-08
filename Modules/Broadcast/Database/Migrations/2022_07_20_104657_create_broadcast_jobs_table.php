<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_20_104657_create_broadcast_jobs_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('broadcast_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId("resource_id")->constrained("broadcast_resources", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->string("type", 32)->index();
            $table->timestamp("created_at");
            $table->unsignedInteger("attempts")->default(0);
            $table->timestamp("last_attempt_at")->nullable();
            $table->string("status", 16)->default("pending")->index();
            $table->json("payload")->nullable();
            $table->json("last_attempt_result")->nullable();
        });
    }
};
