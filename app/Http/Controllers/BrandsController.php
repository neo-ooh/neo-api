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
use Neo\Http\Requests\Brands\AssociateBrandsRequest;
use Neo\Http\Requests\Brands\DestroyBrandRequest;
use Neo\Http\Requests\Brands\ListBrandsRequest;
use Neo\Http\Requests\Brands\StoreBrandRequest;
use Neo\Http\Requests\Brands\StoreBrandsBatchRequest;
use Neo\Http\Requests\Brands\UpdateBrandRequest;
use Neo\Models\Brand;

class BrandsController {
    public function index(ListBrandsRequest $request): Response {
        $brands = Brand::query()
                       ->with([
                           "child_brands:id"
                       ])->get();

        if (in_array("properties", $request->input("with", []), true)) {
            $brands->load("properties.actor.name");
        }

        return new Response($brands);
    }

    public function store(StoreBrandRequest $request) {
        $brand          = new Brand();
        $brand->name_en = $request->input("name_en");
        $brand->name_fr = $request->input("name_fr");
        $brand->save();

        return new Response($brand, 201);
    }

    public function storeBatch(StoreBrandsBatchRequest $request) {
        $brandNames = collect($request->input("names"));
        Brand::query()->insert($brandNames->map(fn($brandName) => [
            "name_en" => $brandName,
            "name_fr" => $brandName,
        ])->toArray());

        $brands = Brand::query()->whereIn("name_en", $brandNames)->get();

        return new Response($brands, 201);
    }

    public function syncChildren(AssociateBrandsRequest $request, Brand $brand) {
        $brands = $request->input("brands");

        $brand->child_brands()->whereNotIn("id", $brands)->update(["parent_id" => null]);
        Brand::query()->whereIn("id", $brands)->update(["parent_id" => $brand->id]);

        return new Response();
    }

    public function update(UpdateBrandRequest $request, Brand $brand) {
        $brand->name_en   = $request->input("name_en");
        $brand->name_fr   = $request->input("name_fr");
        $brand->parent_id = $request->input("parent_id");
        $brand->save();

        return new Response($brand, 200);
    }

    public function destroy(DestroyBrandRequest $request, Brand $brand) {
        $brand->delete();

        return new Response();
    }
}
