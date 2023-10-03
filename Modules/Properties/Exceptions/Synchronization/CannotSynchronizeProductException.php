<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CannotSynchronizeProductException.php
 */

namespace Neo\Modules\Properties\Exceptions\Synchronization;

use Neo\Exceptions\BaseException;

class CannotSynchronizeProductException extends BaseException {
	public function __construct(string $reason = "") {
		parent::__construct($reason, "inventories.cannot-sync-product");
	}
}
