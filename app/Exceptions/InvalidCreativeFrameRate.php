<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidCreativeFrameRate.php
 */

namespace Neo\Exceptions;

class InvalidCreativeFrameRate extends BaseException {
    protected $code = "creative.bad-framerate";
    protected $message = "Creative framerate is not allowed";
}
