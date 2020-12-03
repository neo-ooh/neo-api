<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

/** @noinspection PhpIllegalPsrClassPathInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActorsLocationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('actors_locations',
            function (Blueprint $table) {
                $table->foreignId('actor_id')->constrained("actors")->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('location_id')->constrained("locations")->cascadeOnUpdate()->cascadeOnDelete();
                $table->timestamps();
            });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists('actors_locations');
    }

}
