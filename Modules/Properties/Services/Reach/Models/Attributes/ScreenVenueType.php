<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ScreenVenueType.php
 */

namespace Neo\Modules\Properties\Services\Reach\Models\Attributes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class ScreenVenueType extends Data {
    public function __construct(
        public int             $id,
        public string|Optional $name,
        public int|Optional    $enumeration_id,
    ) {
    }
}
