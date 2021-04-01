<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GeneralConditions.php
 */

namespace Neo\Documents\Contract\Components;

use Illuminate\View\Component;

class GeneralConditions extends Component {
    public function render() {
        return view('documents.contract.general-conditions');
    }
}
