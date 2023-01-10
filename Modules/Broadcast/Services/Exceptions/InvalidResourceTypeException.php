<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidExternalBroadcasterResourceType.php
 */

namespace Neo\Modules\Broadcast\Services\Exceptions;

use RuntimeException;
use Throwable;

class InvalidResourceTypeException extends RuntimeException {
    public function __construct(string $expected, string $found, ?Throwable $previous = null) {
        parent::__construct("Expected a resource of type '$expected', got '$found' instead", -1, $previous);
    }
}
