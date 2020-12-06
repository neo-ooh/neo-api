<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - BroadSignFacade.php
 */

namespace Neo\BroadSign;

use Illuminate\Support\Facades\Facade;

/**
 * Class BroadSignFacade
 *
 * @package Neo\BroadSign
 * @mixin BroadSign
 */
class BroadSignFacade extends Facade {
    protected static function getFacadeAccessor (): string {
        return 'broadsign';
    }
}
