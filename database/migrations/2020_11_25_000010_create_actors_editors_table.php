<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - 2020_11_25_000010_create_actors_editors_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActorsEditorsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create("actors_editors",
            function (Blueprint $table) {
                $table->foreignId("actor_id")
                      ->constrained("actors")
                      ->cascadeOnUpdate()
                      ->cascadeOnDelete();

                $table->foreignId("template_id")
                      ->constrained("editor_templates")
                      ->cascadeOnUpdate()
                      ->cascadeOnDelete();

                $table->json("parameters")->nullable(false);

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::drop("actors_editors");
    }
}
