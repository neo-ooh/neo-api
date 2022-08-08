<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PiSignageConfig.php
 */

namespace Neo\Modules\Broadcast\Services\PiSignage;

use Neo\Modules\Broadcast\Services\BroadcasterConfig;

class PiSignageConfig extends BroadcasterConfig {
    public string $apiURL;

    public string $apiToken;
}
