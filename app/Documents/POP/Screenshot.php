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
use Neo\Models\ContractScreenshot;

class Screenshot {
    public string $city;
    public string $province;

    public string $format;
    public Carbon $created_at;

    public string $url;

    public function __construct(ContractScreenshot $screenshot, bool $mockup) {
        $this->city       = $screenshot->burst->location?->city ?? '-';
        $this->province   = $screenshot->burst->location?->province ?? '-';
        $this->format     = $screenshot->burst->location?->display_type->name ?? '-';
        $this->created_at = $screenshot->created_at->tz("America/Toronto");


        $this->url = $mockup ? $screenshot->mockup_path : $screenshot->url;

        // Mark the screenshot as locked
        $screenshot->is_locked = true;
        $screenshot->save();
    }
}
