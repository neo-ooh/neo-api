<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - 2022_06_23_105156_create_fields_segments_stats_view.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        DB::statement(<<<EOF
            CREATE VIEW fields_segments_stats as 
            SELECT
                `fs`.`id`,
                COUNT(`pfsv`.`property_id`) AS `value_count`,
                MIN(`pfsv`.`index`) AS `min_index`,
                MAX(`pfsv`.`index`) AS `max_index`
            FROM `fields_segments` `fs`
            LEFT JOIN `properties_fields_segments_values` `pfsv` ON `pfsv`.`fields_segment_id` = `fs`.`id`
            GROUP BY `fs`.`id`
            EOF
        );
    }

    public function down() {
        Schema::dropIfExists('fields_segments_stats');
    }
};
