<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidCreativeType.php
 */

namespace Neo\Exceptions;

class InvalidCreativeType extends BaseException {
    protected $code = "creative.invalid-type";
    protected $message = "Creative type must be either `static` or `dynamic`";
}
