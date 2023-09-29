<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatabaseSeeder.php
 */

namespace Neo\Modules\Properties\Database\Seeders;

use Illuminate\Database\Seeder as BaseSeeder;
use Neo\Modules\Properties\Database\Seeders\views\ContractLinesCampaignsViewSeeder;
use Neo\Modules\Properties\Database\Seeders\views\ProductsViewSeeder;
use Neo\Modules\Properties\Database\Seeders\views\ProductsWarningsViewSeeder;
use Neo\Modules\Properties\Database\Seeders\views\PropertiesExtendedViewSeeder;
use Neo\Modules\Properties\Database\Seeders\views\PropertiesViewSeeder;
use Neo\Modules\Properties\Database\Seeders\views\PropertiesWarningsViewSeeder;

class DatabaseSeeder extends BaseSeeder {
	public function run(): void {
		$this->call([
			            // Views
			            ProductsViewSeeder::class,
			            ProductsViewSeeder::class,
			            PropertiesViewSeeder::class,
			            PropertiesExtendedViewSeeder::class,
			            PropertiesWarningsViewSeeder::class,
			            ProductsWarningsViewSeeder::class,

			            ContractLinesCampaignsViewSeeder::class,

			            // Data

		            ]);
	}
}
