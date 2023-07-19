<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2023_07_17_103536_create_census_subdivisions_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('census_subdivisions', function (Blueprint $table) {
            $table->string("id", 7)->primary();
            $table->year("census");
            $table->string("name", 100)->index();
            $table->string("type", 3);
            $table->string("province", 2)->index();
            $table->float("landarea_sqkm");
            $table->string("dissemination_uid", 21);
            $table->geometry("geometry");
        });
    }
};
