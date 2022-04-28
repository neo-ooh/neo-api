<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_04_27_114143_fix_contract_lines_spots_type.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('contracts_lines', function (Blueprint $table) {
            $table->unsignedFloat("spots")->change();
        });
    }

    public function down() {
        Schema::table('contracts_lines', function (Blueprint $table) {
            $table->unsignedInteger("spots")->change();
        });
    }
};
