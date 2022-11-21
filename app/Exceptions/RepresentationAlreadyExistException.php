<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - RepresentationAlreadyExistException.php
 */

namespace Neo\Exceptions;

class RepresentationAlreadyExistException extends BaseException {
    public function __construct(string $resourceName) {
        parent::__construct("A $resourceName like this one already exist.");
    }
}
