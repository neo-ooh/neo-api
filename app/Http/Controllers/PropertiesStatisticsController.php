<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertiesStatisticsController.php
 */

namespace Neo\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Neo\Http\Requests\PropertiesStatistics\GetPropertyStatisticsRequest;
use Neo\Models\Property;

class PropertiesStatisticsController {
    public function show(GetPropertyStatisticsRequest $request, Property $property) {
        $years = $request->input("years");

        $datasets = [];
        foreach($years as $year) {
            $datasets[$year] = match ($request->input("breakdown")) {
                "default" => $this->getDefaultBreakdown($property, $year),
                "market" => $this->getMarketBreakdown($property, $year),
                "product" => $this->getProductBreakdown($property, $year, $request->input("product_id")),
                "network" => $this->getNetworkBreakdown($property, $year, $property->network_id),
            };
        }

        return new Response($datasets);
    }

    public function getMarketBreakdown(Property $property, int $year, ?int $productId = null, ?int $networkId = null) {
        return [
            [
                "type"    => "property",
                "name"    => $property->actor->name,
                "traffic" => $this->getTraffic($year, propertyId: $property->actor_id),
            ],
            [
                "type"    => "market",
                "name_en"    => $property->address->city->market->name_en,
                "name_fr"    => $property->address->city->market->name_fr,
                "traffic" => $this->getTraffic($year, marketId: $property->address->city->market_id, productId: $productId, networkId: $networkId),
            ],
            [
                "type"    => "province",
                "name"    => $property->address->city->province->slug,
                "traffic" => $this->getTraffic($year, provinceId: $property->address->city->province_id, productId: $productId, networkId: $networkId),
            ]
        ];
    }

    public function getProductBreakdown(Property $property, int $year, ?int $productId) {
        return [
            ...$this->getMarketBreakdown($property, $year, productId: $productId),
            [
                "type"    => "product",
                "id"    => $productId,
                "traffic" => $this->getTraffic($year, productId: $productId),
            ]
        ];
    }

    public function getNetworkBreakdown(Property $property, int $year, ?int $networkId) {
        return [
            ...$this->getMarketBreakdown($property, $year, networkId: $networkId),
            [
                "type"    => "network",
                "id"    => $networkId,
                "traffic" => $this->getTraffic($year, networkId: $networkId),
            ]
        ];
    }

    public function getDefaultBreakdown(Property $property, int $year) {
        $datasets   = [];
        $datasets[] = [
            "type"    => "property",
            "id"    => $property->actor_id,
            "name"    => $property->actor->name,
            "traffic" => $this->getTraffic($year, propertyId: $property->actor_id),
        ];

        $nextParent = $property->actor->parent_is_group ? $property->actor->parent : null;

        while ($nextParent !== null) {
            $datasets[] = [
                "type"    => "parent",
                "id"    => $nextParent->id,
                "name"    => $nextParent->name,
                "traffic" => $this->getTraffic($year, parentId: $nextParent->id),
            ];

            $nextParent = $nextParent->parent_is_group ? $nextParent->parent : null;
        }

        return $datasets;
    }

    public function getTraffic(int $year, ?int $propertyId = null, ?int $marketId = null, ?int $provinceId = null, ?int $productId = null, ?int $networkId = null, ?int $parentId = null) {
        $query = DB::table("properties_traffic", "pt")
                   ->select(["pt.year", "pt.month"])
                   ->selectRaw("SUM(IFNULL(`pt`.`traffic`, `pt`.`temporary`)) AS `traffic`")
                   ->where("pt.year", "=", $year)
                   ->groupBy(["pt.year", "pt.month"]);

        // We have our base query, now we will add additional constraints based on the provided parameters
        if (isset($propertyId)) {
            $query->where("pt.property_id", "=", $propertyId);
        }

        if (isset($marketId) || isset($provinceId) || isset($networkId)) {
            $query->join("properties AS p", fn($join) => $join->on("p.actor_id", "=", "pt.property_id"));
        }

        if (isset($marketId) || isset($provinceId)) {
            $query->join("addresses AS addr", fn($join) => $join->on("addr.id", "=", "p.address_id"))
                  ->join("cities AS c", fn($join) => $join->on("c.id", "=", "addr.city_id"));
        }

        if (isset($marketId)) {
            $query->where("c.market_id", "=", $marketId);
        }

        if (isset($provinceId)) {
            $query->where("c.province_id", "=", $provinceId);
        }

        if (isset($productId)) {
            $query->join("odoo_properties_products_categories as oppc", fn($join) => $join->on("oppc.property_id", "=", "pt.property_id"))
                  ->where("oppc.product_category_id", "=", $productId);
        }

        if (isset($networkId)) {
            $query->where("p.network_id", "=", $networkId);
        }

        if (isset($parentId)) {
            $query->join("actors_closures AS ac", function ($join) {
                $join->on("ac.descendant_id", "=", "pt.property_id")
                     ->where("ac.depth", ">", 0);
            })
                  ->where("ac.ancestor_id", "=", $parentId);
        }

        return $query->get()->pluck("traffic");
    }
}
