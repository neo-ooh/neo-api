<?php /*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2020_09_01_000030_create_actors_shares_table.php
 */
/** @noinspection PhpIllegalPsrClassPathInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActorsSharesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('actors_shares',
            function (Blueprint $table) {
                $table->foreignId('sharer_id')->constrained('actors')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreignId('shared_with_id')->constrained('actors')->cascadeOnUpdate()->cascadeOnDelete();
                $table->timestamps();

                $table->primary([ 'sharer_id', 'shared_with_id' ]);
            });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::drop('actors_shares');
    }

}
