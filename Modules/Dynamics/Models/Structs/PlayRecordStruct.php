<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PlayRecordStruct.php
 */

namespace Neo\Modules\Dynamics\Models\Structs;

use Spatie\LaravelData\Data;

class PlayRecordStruct extends Data {
	public function __construct(
		public string $dynamic_name,
		public string $dynamic_version,
		public array  $dynamic_params,
		public array  $logs,
		public string $loaded_at,
		public string $played_at,
		public string $ended_at,
	) {
	}
}
