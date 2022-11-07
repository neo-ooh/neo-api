<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_11_04_155143_create_resource_performances_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcePerformancesTable extends Migration {
    public function up(): void {
        Schema::create('resource_performances', static function (Blueprint $table) {
            $table->foreignId("resource_id")->constrained("broadcast_resources", "id");
            $table->date("recorded_at");
            $table->unsignedBigInteger("repetitions");
            $table->unsignedBigInteger("impressions");
            $table->json("data");

            $table->timestamps();
        });
    }
}
