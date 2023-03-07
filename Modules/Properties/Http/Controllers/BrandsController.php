<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BrandsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Illuminate\Http\Response;
use Neo\Modules\Properties\Http\Requests\Brands\AssociateBrandsRequest;
use Neo\Modules\Properties\Http\Requests\Brands\DestroyBrandRequest;
use Neo\Modules\Properties\Http\Requests\Brands\ListBrandsRequest;
use Neo\Modules\Properties\Http\Requests\Brands\MergeBrandsRequest;
use Neo\Modules\Properties\Http\Requests\Brands\StoreBrandRequest;
use Neo\Modules\Properties\Http\Requests\Brands\StoreBrandsBatchRequest;
use Neo\Modules\Properties\Http\Requests\Brands\UpdateBrandRequest;
use Neo\Modules\Properties\Models\Brand;
use Neo\Modules\Properties\Models\Property;

/**
 * Remove the first and last quote from a quoted string of text
 *
 * @param string $text
 * @return string
 * @link https://stackoverflow.com/a/25353877
 */
function trimQuotes(string $text) {
    return preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $text);
}

class BrandsController {
    public function index(ListBrandsRequest $request): Response {
        $brands = Brand::query()
                       ->with([
                                  "child_brands:id,parent_id",
                              ])->get();

        if (in_array("properties", $request->input("with", []), true)) {
            $brands->load("properties.actor:id,name");
        }

        return new Response($brands);
    }

    public function store(StoreBrandRequest $request) {
        $brand          = new Brand();
        $brand->name_en = trimQuotes($request->input("name_en"));
        $brand->name_fr = trimQuotes($request->input("name_fr"));
        $brand->save();

        return new Response($brand, 201);
    }

    public function storeBatch(StoreBrandsBatchRequest $request) {
        $brandNames = collect($request->input("names"));
        Brand::query()->insert($brandNames->map(fn($brandName) => [
            "name_en" => trimQuotes($brandName),
            "name_fr" => trimQuotes($brandName),
        ])->toArray());

        $brands = Brand::query()->whereIn("name_en", $brandNames)->get();

        return new Response($brands, 201);
    }

    public function show(ListBrandsRequest $request, Brand $brand): Response {
        $brand->load(["child_brands:id,parent_id", "properties.actor:id,name"]);
        return new Response($brand);
    }

    public function syncChildren(AssociateBrandsRequest $request, Brand $brand) {
        $brands = $request->input("brands", []);

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


    public function merge(MergeBrandsRequest $request, Brand $brand) {
        $fromIds = $request->input("brands");

        $properties = Property::query()->whereHas("tenants", function ($query) use ($fromIds) {
            $query->whereIn("id", $fromIds);
        })->get();

        Brand::query()->whereIn("parent_id", $fromIds)->update(["parent_id" => $brand->id]);

        /** @var Property $property */
        foreach ($properties as $property) {
            $property->tenants()->detach($fromIds);
            $property->tenants()->syncWithoutDetaching([$brand->id]);
        }

        Brand::query()->whereIn("id", $fromIds)->delete();

        return new Response($brand->refresh()->load(["child_brands:id,parent_id", "properties.actor:id,name"]));
    }

    public function destroy(DestroyBrandRequest $request, Brand $brand) {
        $brand->delete();

        return new Response();
    }
}
