<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ResourceCastable.php
 */

namespace Neo\Modules\Broadcast\Services;

use Spatie\DataTransferObject\DataTransferObject;

/**
 * @template TResource of DataTransferObject
 */
interface ResourceCastable {
    /**
     * Cast the current object to a broadcast resource
     *
     * @return TResource
     */
    public function toResource(): DataTransferObject;
}
