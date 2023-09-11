<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnsupportedFileFormatException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;

class UnsupportedFileFormatException extends BaseException {
	public function __construct(string $mime) {
		parent::__construct("Creative file format is not support: $mime", "creatives.bad-file-format");
	}
}
