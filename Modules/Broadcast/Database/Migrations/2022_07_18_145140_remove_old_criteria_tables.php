<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_07_18_145140_remove_old_criteria_tables.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::dropColumns("formats_layouts", ["trigger_id", "separation_id"]);
        Schema::dropIfExists("frame_settings_broadsign");

        Schema::dropIfExists("broadsign_triggers");
        Schema::dropIfExists("broadsign_separations");
        Schema::dropIfExists("broadsign_criteria");
    }
};
