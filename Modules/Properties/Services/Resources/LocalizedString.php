<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LocalizedString.php
 */

namespace Neo\Modules\Properties\Services\Resources;

use Spatie\LaravelData\Data;

class LocalizedString extends Data {
    public function __construct(
        /**
         * The string locale's with region: <lang>-<region>
         *
         * @example `fr-CA`; `en-CA`; `en-US`
         */
        public string $locale,

        /**
         * The actual string
         */
        public string $value,
    ) {

    }
}
