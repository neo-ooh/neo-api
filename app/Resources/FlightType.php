<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - FlightType.php
 */

namespace Neo\Resources;

enum FlightType: string {
	case Guaranteed = "guaranteed";
	case Bonus = "bonus";
	case BUA = "bua";

	case Mobile = "mobile";
}
