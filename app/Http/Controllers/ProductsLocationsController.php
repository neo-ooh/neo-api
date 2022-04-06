<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsLocationsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\ProductLocations\UpdateProductLocationsRequest;
use Neo\Models\Product;

class ProductsLocationsController {
    public function sync(UpdateProductLocationsRequest $request, Product $product) {
        $product->locations()->sync($request->input("locations", []));

        return new Response($product->locations);
    }
}
