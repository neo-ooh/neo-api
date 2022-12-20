<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DisplayType.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

use Neo\Modules\Broadcast\Enums\ExternalResourceType;

class DisplayType extends ExternalBroadcasterResourceId {
    public function __construct(
        int           $broadcaster_id,
        string        $external_id,
        public string $name,
        /**
         * @var int Width of the screen in pixels
         */
        public int    $width_px,
        /**
         * @var int Height of the screen in pixels
         */
        public int    $height_px,
    ) {
        parent::__construct(
            broadcaster_id: $broadcaster_id,
            external_id   : $external_id,
            type          : ExternalResourceType::DisplayType,
        );
    }
}
