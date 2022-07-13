<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
<<<<<<< HEAD:Modules/Broadcast/Services/DoNotCompare.php
 * @neo/api - DoNotCompare.php
 */

namespace Neo\Modules\Broadcast\Services;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DoNotCompare {
=======
 * @neo/api - CreativeType.php
 */

namespace Neo\Modules\Broadcast\Enums;

enum CreativeType: string {
    case Static = 'static';
    case Url = 'url';
>>>>>>> a886c87f (Database migration for campaigns V2):Modules/Broadcast/Enums/CreativeType.php
}
