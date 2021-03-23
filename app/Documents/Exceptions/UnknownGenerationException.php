<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnknownGenerationException.php
 */

namespace Neo\Documents\Exceptions;

use Neo\Exceptions\BaseException;

class UnknownGenerationException extends BaseException {
    protected $code = "documents.generation-error";
    protected $message = "An unknown error happened while generating the document.";
}
