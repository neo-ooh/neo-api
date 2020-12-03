<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - $file.filePath
 */

namespace Neo\BroadSign;

use Illuminate\Support\Facades\Facade;

class BroadSignFacade extends Facade {
    protected static function getFacadeAccessor (): string {
        return 'broadsign';
    }
}
