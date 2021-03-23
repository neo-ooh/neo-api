<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - IncompatibleFrameAndFormat.php
 */

namespace Neo\Exceptions;

class IncompatibleFrameAndFormat extends BaseException {
    protected $code = "format.incompatible-frame";
    protected $message = "Frame and format are not compatible";
}
