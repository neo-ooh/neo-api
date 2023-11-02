<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SearchResult.php
 */

namespace Neo\Http\Resources;

use Spatie\LaravelData\Data;

class SearchResult extends Data {
	public function __construct(
		public int      $id,
		public string   $type,
		public string   $subtype,
		public string   $label,

		public int|null $parent_id,

		public          $model,
	) {
	}
}
