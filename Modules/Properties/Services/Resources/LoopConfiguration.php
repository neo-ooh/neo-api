<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LoopConfiguration.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Spatie\LaravelData\Data;

class LoopConfiguration extends Data {
    public function __construct(
        public int $loop_length_ms,
        public int $spot_length_ms,
    ) {
    }

    public function spotsCount(): int {
        return (int)round($this->loop_length_ms / $this->spot_length_ms);
    }
}
