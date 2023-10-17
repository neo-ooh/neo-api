<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherCondition.php
 */

namespace Neo\Modules\Dynamics\Services\Weather;

enum WeatherCondition: string {
	case Cloudy = "cloudy";
	case Fog = "fog";
	case HeavyRain = "heavy-rain";
	case LightRain = "light-rain";
	case MostlySunny = "mostly-sunny";
	case RainAndSun = "rain-and-sun";
	case Rain = "rain";
	case Snow = "snow";
	case Sunny = "sunny";
	case Thunderstorm = "thunderstorm";
}
