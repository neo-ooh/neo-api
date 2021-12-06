<?php

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
