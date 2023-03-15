<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidInventoryAdapterException.php
 */

namespace Neo\Modules\Properties\Services\Exceptions;

use Neo\Exceptions\BaseException;

class InvalidInventoryAdapterException extends BaseException {
    public function __construct($type = "") {
        parent::__construct("Invalid inventory service '$type'");
    }
}
