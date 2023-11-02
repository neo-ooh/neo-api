<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - config.php
 */

return [
	"name" => "Dynamics",

	"weather" => [
		"api-key" => env('WEATHER_API_KEY'),

		"reports-ttl-minutes" => env('WEATHER_REPORT_TTL_MINUTES', 5),
	],
];
