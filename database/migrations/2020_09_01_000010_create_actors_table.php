<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActorsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up (): void {
        Schema::create('actors',
            function (Blueprint $table) {
                $table->id();
                $table->string('email')->nullable()->unique();
                $table->string('name', 64)->index();
                $table->string('password', 256)->nullable();
                $table->boolean('is_group')->default(0)->index();
                $table->boolean('is_locked')->default(0);
                $table->foreignId('locked_by')->nullable()->constrained('actors');
                $table->foreignId('branding_id')->nullable()->constrained('brandings');
                $table->boolean('tos_accepted')->default(0);
                $table->timestamp('last_login_at')->nullable();
                $table->timestamps();
            });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down (): void {
        Schema::dropIfExists('actors');
    }

}
