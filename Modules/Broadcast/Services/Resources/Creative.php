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

class Creative extends ExternalBroadcasterResource {
    public string $name;
    public string $fileName;
    public CreativeType $type;

    public int $width;
    public int $height;

    public int $length_ms;

    /**
     * @var string file path to the creative file, will not be set for Url creatives
     */
    public string $path;

    /**
     * @var string file extension of the creative file, will not be set for Url creatives
     */
    public string|null $extension;

    /**
     * @var string Url to the creative
     */
    public string $url;

    public int $refresh_rate_minutes;

    /**
     * @var array<Tag>
     */
    public array $tags;
}
