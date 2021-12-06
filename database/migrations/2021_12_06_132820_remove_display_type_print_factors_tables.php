<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2021_12_06_132820_remove_display_type_print_factors_tables.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemoveDisplayTypePrintFactorsTables extends Migration {
    public function up() {
        Schema::drop("display_types_factors");
        Schema::drop("display_types_prints_factors");
    }

    public function down() {
    }
}
