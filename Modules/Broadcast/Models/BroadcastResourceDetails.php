<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastResourceDetails.php
 */

namespace Neo\Modules\Broadcast\Models;

use Neo\Models\DBView;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;

/**
 * @property int                   $id
 * @property BroadcastResourceType $type
 * @property string|null           $name
 * @property int|null              $access_id
 */
class BroadcastResourceDetails extends DBView {
    protected $table = "broadcast_resources_details";

    protected $casts = [
        "type" => BroadcastResourceType::class,
    ];
}
