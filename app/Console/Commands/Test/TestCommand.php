<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - TestCommand.php
 */

namespace Neo\Console\Commands\Test;

use Illuminate\Console\Command;
use Neo\Jobs\Traffic\EstimateWeeklyTrafficFromMonthJob;
use Neo\Modules\Properties\Models\Property;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
	protected $signature = 'test:test';

	protected $description = 'Internal tests';

	/**
	 * @return void
	 * @throws Exception
	 */
	public function handle() {
		$otgProperties = Property::fromQuery("
			WITH `adapt_properties` AS (
  SELECT `properties`.`actor_id` as 'id'
  FROM `properties`
  JOIN `actors_closures` ON `properties`.`actor_id` = `actors_closures`.`descendant_id`
  WHERE `actors_closures`.`ancestor_id` = 1344
    AND `actors_closures`.`depth` > 0
)
SELECT *
  FROM `properties` `p`
 WHERE `p`.`network_id` = 3
   AND `p`.`actor_id` NOT IN (1277)
   AND `p`.`actor_id` NOT IN (SELECT `id` FROM `adapt_properties`);
		");

		$otgProperties->load("actor", "traffic.monthly_data");


		foreach ($otgProperties as $k => $property) {
			$this->output->section("#$k - " . $property->actor->name);
			foreach ($property->traffic->monthly_data as $monthly_datum) {
				$this->output->comment($monthly_datum->year . "-" . $monthly_datum->month);
				
				$j = new EstimateWeeklyTrafficFromMonthJob($property->getKey(), $monthly_datum->year, $monthly_datum->month);
				$j->handle();
			}
		}
	}
}
