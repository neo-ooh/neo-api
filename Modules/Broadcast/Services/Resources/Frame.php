<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Frame.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

use Neo\Modules\Broadcast\Enums\ExternalResourceType;

class Frame extends ExternalBroadcasterResourceId {
    public ExternalResourceType $type = ExternalResourceType::Frame;

    public string $name;

    /**
     * px
     */
    public int $width;

    /**
     * px
     */
    public int $height;
}
