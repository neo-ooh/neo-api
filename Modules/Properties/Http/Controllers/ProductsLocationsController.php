<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductsLocationsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Http\Requests\ProductLocations\UpdateProductLocationsRequest;
use Neo\Modules\Properties\Models\Product;

class ProductsLocationsController {
    public function sync(UpdateProductLocationsRequest $request, Product $product) {
        $product->locations()->sync($request->input("locations", []));

        return new Response($product->locations);
    }
}
