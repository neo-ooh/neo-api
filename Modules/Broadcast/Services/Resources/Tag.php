<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Tag.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

use Neo\Modules\Broadcast\Enums\BroadcastTagType;
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Services\DoNotCompare;

class Tag extends ExternalBroadcasterResourceId {
    public ExternalResourceType $type = ExternalResourceType::Tag;

    #[DoNotCompare]
    public string $name;

    #[DoNotCompare]
    public BroadcastTagType $tag_type;
}
