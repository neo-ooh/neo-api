<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\Exceptions;

class InvalidCreativeDuration extends BaseException {
    protected $code = "creative.bad-duration";
    protected $message = "Creative's duration doesn't match its content duration";
}
