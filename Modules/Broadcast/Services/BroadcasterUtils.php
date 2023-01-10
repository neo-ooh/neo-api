<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterUtils.php
 */

namespace Neo\Modules\Broadcast\Services;

use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\Exceptions\InvalidExternalBroadcasterResourceType;
use Neo\Modules\Broadcast\Services\Exceptions\InvalidResourceTypeException;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;

class BroadcasterUtils {
    /**
     * Extract ids from an `ExternalBroadcasterResourceId` array, optionnaly validating the type of the resources a
     * the same type
     *
     * @param array<ExternalBroadcasterResourceId> $resources
     * @param ExternalResourceType|null            $type
     * @return array<string>
     */
    public static function extractExternalIds(array $resources, ExternalResourceType|null $type = null): array {
        /** @var array<string> $ids */
        $ids = [];

        foreach ($resources as $resource) {
            if (!($resource instanceof ExternalBroadcasterResourceId)) {
                throw new InvalidResourceTypeException("ExternalBroadcasterResourceId", gettype($resource));
            }


            if ($type && $resource->type !== $type) {
                throw new InvalidExternalBroadcasterResourceType(expected: $type, found: $resource->type);
            }

            $ids[] = $resource->external_id;
        }

        return $ids;
    }
}
