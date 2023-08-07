<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPScreenshot.php
 */

namespace Neo\Modules\Properties\Documents\POP;

use Spatie\LaravelData\Data;

class POPScreenshot extends Data {
	public function __construct(
		public int  $screenshot_id,
		public bool $mockup,
	) {
	}
}
