<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - POPCoverPage.php
 */

namespace Neo\Modules\Properties\Documents\POP\components;

use Carbon\Carbon;
use Illuminate\View\Component;

class POPCoverPage extends Component {
    public function __construct() {
    }

    public function render() {
        return view('properties::pop.coverpage', [
            "date" => Carbon::now()->toDateString(),
        ])->render();
    }
}
