<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
* @neo/api - CreativeType.php
*/

namespace Neo\Modules\Broadcast\Enums;

enum CreativeType: string {
    case Static = 'static';
    case Url = 'url';
}
