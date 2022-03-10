<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsFillStrategy.php
 */

namespace Neo\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static self digital()
 * @method static self static ()
 */
final class ProductsFillStrategy extends Enum {
    public const digital = "DIGITAL";
    public const static = "STATIC";
}
