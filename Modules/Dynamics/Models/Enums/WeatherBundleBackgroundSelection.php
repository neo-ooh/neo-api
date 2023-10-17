<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - WeatherBundleBackgroundSelection.php
 */

namespace Neo\Modules\Dynamics\Models\Enums;

enum WeatherBundleBackgroundSelection: string {
	case Weather = "weather";
	case Random = "random";
}
