<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - InvalidCreativeFileFormat.php
 */

namespace Neo\Exceptions;

class InvalidCreativeFileFormat extends BaseException {
    protected $code = "creative.invalid-file-format";
    protected $message = "The creative format doesn't match its content";
}
