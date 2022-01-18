<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BrandsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Http\Requests\Brands\DestroyBrandRequest;
use Neo\Http\Requests\Brands\ListBrandsRequest;
use Neo\Http\Requests\Brands\StoreBrandRequest;
use Neo\Http\Requests\Brands\UpdateBrandRequest;
use Neo\Models\Brand;

class BrandsController {
    public function index(ListBrandsRequest $request): Response {
        $brands = Brand::query()->orderBy("name")->get();
        return new Response($brands);
    }

    public function store(StoreBrandRequest $request) {
        $brand       = new Brand();
        $brand->name = $request->input("name");
        $brand->save();

        return new Response($brand, 201);
    }

    public function update(UpdateBrandRequest $request, Brand $brand) {
        $brand->name = $request->input("name");
        $brand->save();

        return new Response($brand, 200);
    }

    public function destroy(DestroyBrandRequest $request, Brand $brand) {
        $brand->delete();

        return new Response();
    }
}
