<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Traffic.php
 */

namespace Neo\Services\Traffic;

use InvalidArgumentException;
use Neo\Modules\Properties\Models\TrafficSource;

abstract class Traffic {
    public const LINKETT = "linkett";

    public static function from(TrafficSource $source) {
        switch ($source->type) {
            case static::LINKETT:
                return new LinkettAPIAdapter($source->settings);
        }

        throw new InvalidArgumentException("Traffic source type $source->type is not supported");
    }
}
