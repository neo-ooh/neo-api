<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Network.php
 */

namespace Neo\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static self shopping()
 * @method static self fitness()
 * @method static self otg()
 */
final class Network extends Enum {
    public const shopping = "NETWORK_SHOPPING";
    public const fitness = "NETWORK_FITNESS";
    public const otg = "NETWORK_OTG";
}
