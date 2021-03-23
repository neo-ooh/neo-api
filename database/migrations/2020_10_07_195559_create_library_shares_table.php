<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2020_10_07_195559_create_library_shares_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLibrarySharesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('library_shares',
            function (Blueprint $table) {
                $table->foreignId("library_id")->constrained('libraries')->onDelete('cascade');
                $table->foreignId("actor_id")->constrained('actors')->onDelete('cascade');
                $table->timestamps();

                $table->primary([ "library_id", "actor_id" ]);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists('library_shares');
    }
}
