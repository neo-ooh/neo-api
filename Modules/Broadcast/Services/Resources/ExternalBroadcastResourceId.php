<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastServiceCreatives.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

use Spatie\DataTransferObject\DataTransferObject;

class ExternalBroadcastResourceId extends DataTransferObject {
    public string $external_id;
}
