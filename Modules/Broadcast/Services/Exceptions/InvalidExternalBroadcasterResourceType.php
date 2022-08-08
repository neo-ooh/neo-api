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

use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use RuntimeException;
use Throwable;

class InvalidExternalBroadcasterResourceType extends RuntimeException {
    public function __construct(ExternalResourceType $expected, ExternalResourceType $found, ?Throwable $previous = null) {
        parent::__construct("Expected an `ExternalBroadcasterResourceId` of type '$expected->name($expected->value)', '$found->name($found->value)' found", -1, $previous);
    }
}
