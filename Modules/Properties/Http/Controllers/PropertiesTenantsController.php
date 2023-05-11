<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesTenantsController.php
 */

namespace Neo\Modules\Properties\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Neo\Modules\Properties\Http\Requests\PropertiesTenants\ImportTenantsRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesTenants\ListTenantsRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesTenants\RemoveTenantRequest;
use Neo\Modules\Properties\Http\Requests\PropertiesTenants\SyncTenantsRequest;
use Neo\Modules\Properties\Models\Brand;
use Neo\Modules\Properties\Models\Property;
use Normalizer;

function normalizeStr(string $str): string {
    $str = strtolower($str);
    $str = Normalizer::normalize($str, Normalizer::NFD);
    $str = preg_replace("/[\u{0300}-\u{036f}]/", "", $str);
    return preg_replace("/[^\w\s]/i", "", $str);
}

class PropertiesTenantsController {
    public function index(ListTenantsRequest $request, Property $property): Response {
        return new Response($property->tenants);
    }

    public function sync(SyncTenantsRequest $request, Property $property): Response {
        $property->tenants()->sync($request->input("tenants", []));

        $property->last_review_at = Carbon::now();
        $property->save();

        return new Response($property->tenants);
    }

    public function import(ImportTenantsRequest $request, Property $property) {

        /** @var Collection<array{name: string, normalized_name: string}> $normalizedTenants */
        $normalizedTenants = collect($request->input("tenants", []))
            ->map(fn(string $tenantName) => [
                "name"            => $tenantName,
                "normalized_name" => normalizeStr($tenantName),
            ]);

        /** @var Collection<Brand> $normalizedBrands */
        $normalizedBrands = Brand::query()->get()->map(function (Brand $brand) {
            $brand->name_en = normalizeStr($brand->name_en);
            $brand->name_fr = normalizeStr($brand->name_fr);
            return $brand;
        });

        $matchedBrands    = collect();
        $nonMatchedBrands = collect();

        /** @var array{name: string, normalized_name: string} $tenant */
        foreach ($normalizedTenants as $tenant) {
            /** @var Brand|null $brand */
            $brand = $normalizedBrands->first(fn(Brand $brand) => $brand->name_en === $tenant["normalized_name"] || $brand->name_fr === $tenant["normalized_name"]);

            if ($brand) {
                $matchedBrands[] = $brand->getKey();
                continue;
            }

            $nonMatchedBrands[] = $tenant;
        }

        // Insert matched brands
        /** @var Collection<number> $propertyTenants */
        $propertyTenants = $property->tenants()->pluck("id")->merge($matchedBrands)->unique();
        $property->tenants()->sync($propertyTenants);

        // For non-matched brands, we add them to the DB, and then to the property
        $newBrandsToAttach = [];
        foreach ($nonMatchedBrands as $newBrand) {
            $brand          = new Brand();
            $brand->name_en = $newBrand["name"];
            $brand->name_fr = $newBrand["name"];
            $brand->save();

            $newBrandsToAttach[] = $brand->getKey();
        }

        $property->tenants()->attach($newBrandsToAttach);

        return new Response($property->tenants);
    }

    public function remove(RemoveTenantRequest $request, Property $property, Brand $brand): Response {
        $property->tenants()->detach($brand->id);

        return new Response();
    }
}
