<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsViewSeeder.php
 */

namespace Neo\Modules\Properties\Database\Seeders\views;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsViewSeeder extends Seeder {
	public function run() {
		$viewName = "products_view";

		DB::statement("DROP VIEW IF EXISTS $viewName");

		DB::statement(<<<EOS
		CREATE VIEW $viewName AS
				  SELECT
				`p`.`id`,
				`p`.`inventory_resource_id`,
				`p`.`property_id`,
				`p`.`category_id`,
				`pc`.`type` as 'type',
				`p`.`name_en`,
				`p`.`name_fr`,
				COALESCE(`p`.`format_id`, `pc`.`format_id`) as 'format_id',
				COALESCE(`p`.`site_type_id`, `pr`.`type_id`) as 'site_type_id',
				`p`.`quantity`,
				`p`.`is_sellable`,
				`p`.`unit_price`,
				COALESCE(`p`.`programmatic_price`, `pc`.`programmatic_price`) as 'programmatic_price',
				`p`.`notes`,
				`p`.`is_bonus`,
				`p`.`linked_product_id`,
				COALESCE(NULLIF(`p`.`allowed_media_types`, ''), `pc`.`allowed_media_types`) as 'allowed_media_types',
				COALESCE(`p`.`allows_audio`, `pc`.`allows_audio`) as 'allows_audio',
				COALESCE(`p`.`allows_motion`, `pc`.`allows_motion`) as 'allows_motion',
				COALESCE(`p`.`production_cost`, `pc`.`allows_motion`) as 'production_cost',
				COALESCE(`p`.`screen_size_in`, `pc`.`screen_size_in`) as 'screen_size_in',
				COALESCE(`p`.`screen_type_id`, `pc`.`screen_type_id`) as 'screen_type_id',
				COALESCE(`p`.`cover_picture_id`, `pc`.`cover_picture_id`) as 'cover_picture_id',
				COUNT(`ip`.`id`) as `pictures_count`,
				`p`.`created_at`,
				`p`.`updated_at`,
				`p`.`deleted_at`
				FROM `products` `p`
				JOIN `products_categories` `pc` ON `p`.`category_id` = `pc`.`id`
				JOIN `properties` `pr` ON `p`.`property_id` = `pr`.`actor_id`        
				LEFT JOIN `inventory_pictures` `ip` ON `p`.`id` = `ip`.`product_id`      
				GROUP BY `p`.`id`  
		EOS
		);
	}
}
