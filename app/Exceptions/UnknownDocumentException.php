<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - UnknownDocumentException.php
 */

namespace Neo\Exceptions;

class UnknownDocumentException extends BaseException {
    protected int $status = 400;
    protected $code = "document.unknown";
    protected $message = "Unknown document";
}

