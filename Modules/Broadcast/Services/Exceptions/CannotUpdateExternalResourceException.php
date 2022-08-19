<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CannotUpdateExternalResourceException.php
 */

namespace Neo\Modules\Broadcast\Services\Exceptions;

use Neo\Modules\Broadcast\Services\BroadcasterType;
use RuntimeException;
use Throwable;

/**
 * Thrown when an external resource cannot be updated.
 * Some broadcaster does not allow updating some properties of a resource once it's been created
 */
class CannotUpdateExternalResourceException extends RuntimeException {
    public function __construct(BroadcasterType $service, string $message, ?Throwable $previous = null) {
        parent::__construct("[$service->value] $message", -1, $previous);
    }
}
