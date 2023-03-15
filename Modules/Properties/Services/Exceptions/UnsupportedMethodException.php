<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnsupportedMethodException.php
 */

namespace Neo\Modules\Properties\Services\Exceptions;

use Neo\Exceptions\BaseException;

class UnsupportedMethodException extends BaseException {
    public function __construct(string $type) {
        parent::__construct("Inventory of type `$type` does not support this action");
    }
}
