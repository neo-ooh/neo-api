<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsLoopConfigurationsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\LoopConfigurations\SyncLoopConfigurationsRequest;
use Neo\Models\Product;

class ProductsLoopConfigurationsController {
    public function sync(SyncLoopConfigurationsRequest $request, Product $product) {
        $product->loop_configurations()->sync($request->input("ids", []));

        return new Response(["status" => "ok"]);
    }
}
