<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_18_155214_create_census_federal_electoral_districts_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('census_federal_electoral_districts', function (Blueprint $table) {
            $table->string('id', 5)->primary();
            $table->year('census');
            $table->string('name_en', 100);
            $table->string('name_fr', 100);
            $table->string('province', 2)->index();
            $table->float('landarea_sqkm');
            $table->string('dissemination_uid', 21);
            $table->geometry('geometry');
        });
    }
};
