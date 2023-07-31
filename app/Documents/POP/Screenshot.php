<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Screenshot.php
 */

namespace Neo\Documents\POP;

use Carbon\Carbon;
use Neo\Models\Screenshot as ScreenshotModel;

class Screenshot {
    public string $city;
    public string $province;

    public string $format;
    public Carbon $created_at;

    public string $url;

    public function __construct(ScreenshotModel $screenshot, bool $mockup) {
        $location = $screenshot->location ?? $screenshot->player->location;

        $this->city       = $location->city ?? '-';
        $this->province   = $location->province ?? '-';
        $this->format     = $location->display_type->name ?? '-';
        $this->created_at = $screenshot->received_at->tz("America/Toronto");

        $this->url = $mockup ? $screenshot->mockup_path : $screenshot->url;
    }
}
