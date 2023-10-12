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
use Neo\Models\City;
use Neo\Modules\Dynamics\Services\Weather\WeatherSourceClient;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
	protected $signature = 'test:test';

	protected $description = 'Internal tests';

	/**
	 * @return void
	 * @throws Exception
	 */
	public function handle() {
		$client = new WeatherSourceClient();
		/** @var City $city */
		$city = City::query()->find(1);
		
		dump($client->getWeather($city->geolocation->getCoordinates()[0], $city->geolocation->getCoordinates()[1]));
	}
}
