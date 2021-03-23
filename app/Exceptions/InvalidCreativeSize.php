<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidCreativeSize.php
 */

namespace Neo\Exceptions;

class InvalidCreativeSize extends BaseException {
    protected $code = "creative.too-heavy";
    protected $message = "Creative size is too high.";
}
