<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcasterConfig.php
 */

namespace Neo\Modules\Broadcast\Services;

abstract class BroadcasterConfig {
    public BroadcasterType $type;
    public string $name;

    public int $connectionID;
    public string $connectionUUID;

    public int $networkID;
    public string $networkUUID;

    public string $apiURL;
}
