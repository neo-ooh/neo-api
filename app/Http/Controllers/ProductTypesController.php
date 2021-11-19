<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductTypesController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ProductTypes\ListProductTypesRequest;
use Neo\Models\ProductType;

class ProductTypesController {
    public function index(ListProductTypesRequest $request) {
        $relations = $request->input("with", []);

        $productTypes = ProductType::all();

        if (in_array("product_categories", $relations, true)) {
            $productTypes->loadMissing("categories");
        }

        return new Response($productTypes);
    }

    public function store() {
        //
    }

    public function show() {
        //
    }

    public function update() {
        //
    }

    public function destroy() {
        //
    }
}
