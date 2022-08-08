<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalBroadcastResourceNotFoundException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

class ExternalBroadcastResourceNotFoundException extends BaseException {
    public function __construct(public ExternalBroadcasterResourceId $resourceId) {
        $typeStr = ucfirst($resourceId->type->value);
        parent::__construct("$typeStr #$resourceId->external_id could not be found");
    }
}
