<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidCreativeSize.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class InvalidCreativeSize extends BaseException {
	public function __construct(false|int $sizeInBytes) {
		if ($sizeInBytes !== false) {
			$sizeInKB = $sizeInBytes / 1024;
			$message  = "Creative size is too big : $sizeInKB/1024";
			$code     = "creatives.too-heavy";
		} else {
			$message = "Unknown Creative size";
			$code    = "creatives.unknown-size";
		}

		parent::__construct($message, $code);
	}
}
