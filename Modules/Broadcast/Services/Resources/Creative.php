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

use Neo\Modules\Broadcast\Enums\CreativeType;

class Creative extends ExternalBroadcastResource {
    public string $name;
    public CreativeType $type;

    public int $width;
    public int $height;

    public int $length;

    public int $url;
    public string|null $extension;
}
