<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastResourceDoesNotSupportPromoteException.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;

class BroadcastResourceDoesNotSupportPromoteException extends BaseException {
    public function __construct(BroadcastResourceType $type) {
        parent::__construct("Broadcast resource of type $type->name does not support `Promote`.", "broadcast-resource.cannot-promote");
    }
}
