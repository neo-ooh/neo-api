<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Content.php
 */

namespace Neo\Modules\Broadcast\Services\Resources;

class Content extends ExternalBroadcasterResource {
    public string $name;

    public int $duration_msec;
    public bool $fullscreen;
}
