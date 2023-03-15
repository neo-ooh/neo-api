<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductType.php
 */

namespace Neo\Modules\Properties\Enums;

enum ProductType: string {
    case Digital = "DIGITAL";
    case Static = "STATIC";
    case Specialty = "SPECIALTY";
}
