<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CannotOverwriteCreativeException.php
 */

namespace Neo\Exceptions;

class CannotOverwriteCreativeException extends BaseException {
    protected $code = "creative.already-exist";
    protected $message = "A creative is already present";
}
