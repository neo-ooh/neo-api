<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_02_08_140856_create_tags_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagsTable extends Migration {
    public function up() {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();

            $table->string("name", 45)->unique();
            $table->string("color", 6)->nullable();

            $table->timestamps();
        });

        Schema::create("actors_tags", function (Blueprint $table) {
            $table->foreignId("actor_id")->constrained("actors", "id")->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId("tag_id")->constrained("tags", "id")->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down() {
        Schema::dropIfExists('tags');
        Schema::dropIfExists('actors_tags');
    }
}
