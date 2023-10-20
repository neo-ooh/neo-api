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
use Neo\Modules\Properties\Enums\MediaType;
use Neo\Modules\Properties\Models\Product;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class TestCommand extends Command {
	protected $signature = 'test:test';

	protected $description = 'Internal tests';

	/**
	 * @return void
	 * @throws Exception
	 */
	public function handle() {
		$product  = Product::find(448);
		$resource = $product->toResource(6);

		dump(
			[
				...(in_array(MediaType::Image, $resource->allowed_media_types) ? ["image/jpeg", "image/png"] : []),
				...(in_array(MediaType::Video, $resource->allowed_media_types) ? ["video/mp4"] : []),
				...(in_array(MediaType::HTML, $resource->allowed_media_types) ? ["text/html"] : []),
			]
		);
	}
}
