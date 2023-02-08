<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsFillStrategy.php
 */

namespace Neo\Modules\Properties\Enums;

enum ProductsFillStrategy: string {
    case digital = "DIGITAL";
    case static = "STATIC";
    case specialty = "SPECIALTY";
}
