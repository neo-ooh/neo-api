<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MissingExternalResourceException.php
 */

namespace Neo\Modules\Broadcast\Services\Exceptions;

use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\BroadcasterType;
use Throwable;

class MissingExternalResourceException extends BroadcastServiceException {
    public function __construct(BroadcasterType $service, ExternalResourceType $type, string $message = "", ?Throwable $previous = null) {
        parent::__construct($service, $message ? "$type->value - $message" : "External $type->value required but none provided.", $previous);
    }
}
