<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LibraryStorageFullException.php
 */

namespace Neo\Exceptions;

class LibraryStorageFullException extends BaseException {
    protected $code = "library.full";
    protected $message = "Library at full capacity";
}
