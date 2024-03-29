<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesViewSeeder.php
 */

namespace Neo\Modules\Properties\Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertiesViewSeeder extends Seeder {
	public function run() {
		$viewName = "properties_view";

		DB::statement("DROP VIEW IF EXISTS $viewName");

		DB::statement(/** @lang SQL */ <<<EOS
        CREATE VIEW $viewName AS
        SELECT `p`.*,
               `a`.name as `name`
        FROM `properties` `p`
           JOIN `actors` `a` ON `p`.`actor_id` = `a`.`id`
        GROUP BY `p`.`actor_id`        
        EOS
		);
	}
}
