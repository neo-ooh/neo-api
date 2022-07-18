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
<<<<<<< HEAD
        Schema::table("formats_layouts", static function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->dropConstrainedForeignId("trigger_id");
            $table->dropConstrainedForeignId("separation_id");
        });
        Schema::dropIfExists("frame_settings_broadsign");
        Schema::dropIfExists("frame_settings_pisignage");
=======
        Schema::dropColumns("formats_layouts", ["trigger_id", "separation_id"]);
        Schema::dropIfExists("frame_settings_broadsign");
>>>>>>> 6550a640 (Add tags migrations)

        Schema::dropIfExists("broadsign_triggers");
        Schema::dropIfExists("broadsign_separations");
        Schema::dropIfExists("broadsign_criteria");
    }
};
