<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_05_06_110214_drop_leftover_tables.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::drop("editor_templates");
        Schema::drop("actors_editors");
        Schema::drop("checksum");
    }

    public function down() {
    }
};
