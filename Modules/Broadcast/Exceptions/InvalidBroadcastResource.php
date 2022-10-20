<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InvalidBroadcastResource.php
 */

namespace Neo\Modules\Broadcast\Exceptions;

use Neo\Exceptions\BaseException;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;

class InvalidBroadcastResource extends BaseException {
    /**
     * @param ExternalResourceType   $given
     * @param ExternalResourceType[] $expecteds
     */
    public function __construct(public ExternalResourceType $given, public array $expecteds) {
        $givenStr   = ucfirst($this->given->value);
        $expecedStr = implode(", ", array_map(fn(ExternalResourceType $r) => ucfirst($r->value), $this->expecteds));

        parent::__construct("Invalid external resource. Expected $expecedStr, found $givenStr", "resources.invalid-resource");
    }
}
