<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DatabaseSeeder.php
 */

namespace Neo\Modules\Broadcast\Database\Seeders;

use Illuminate\Database\Seeder as BaseSeeder;
use Neo\Modules\Broadcast\Database\Seeders\views\BroadcastResourcesDetailsViewSeeder;
use Neo\Modules\Broadcast\Database\Seeders\views\CampaignFormatsSeeder;
use Neo\Modules\Broadcast\Database\Seeders\views\SchedulesDetailsViewSeeder;

class DatabaseSeeder extends BaseSeeder {
	public function run(): void {
		$this->call([
			            // Views
			            CampaignFormatsSeeder::class,
			            SchedulesDetailsViewSeeder::class,
			            BroadcastResourcesDetailsViewSeeder::class,

			            // Data
			            ParametersSeeder::class,
		            ]);
	}
}
