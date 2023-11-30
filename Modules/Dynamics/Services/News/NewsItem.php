<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NewsItem.php
 */

namespace Neo\Modules\Dynamics\Services\News;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class NewsItem extends Data {
	public function __construct(
		public string      $id,
		public string      $category_slug,
		public string      $headline,
		public Carbon      $date,
		public string|null $media_path,
	) {
	}
}
