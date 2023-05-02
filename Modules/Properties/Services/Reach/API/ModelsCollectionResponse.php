<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ModelsCollectionResponse.php
 */

namespace Neo\Modules\Properties\Services\Reach\API;

use Spatie\LaravelData\Data;

class ModelsCollectionResponse extends Data {
    public function __construct(
        public int         $count,
        public string|null $next,
        public string|null $previous,
        public array       $results,
    ) {
    }
}
