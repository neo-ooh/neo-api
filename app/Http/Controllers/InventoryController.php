<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - InventoryController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Neo\BroadSign\BroadSign;

class InventoryController extends Controller {
    /**
     * @param Broadsign $broadsign
     *
     * @return ResponseFactory|Response
     */
    public function index (BroadSign $broadsign) {
        $inventory = $broadsign->getInventoryReport(2021);

        return new Response($inventory);
    }
}
