<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PiSignageConfig.php
 */

namespace Neo\Services\Broadcast\PiSignage;

use Neo\Services\Broadcast\Broadcaster;

class PiSignageConfig {
    public string $broadcaster = Broadcaster::PISIGNAGE;

    public int $connectionID;

    public string $connectionUUID;

    public int $networkID;

    public string $networkUUID;

    public string $apiURL;

    public string $apiToken;
}
