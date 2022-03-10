<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Product.php
 */

namespace Neo\Documents\PlannerExport;

use Illuminate\Support\Collection;

class Product {
    public int $id;
    public \Neo\Models\Product $product;

    public int $faces;
    public int $spots;
    public int $impressions;
    public float $mediaValue;
    public float $unitPrice;
    public float $price;
    public int $isDiscounted;
    public float $cpm;

    public Collection $products;

    public function __construct(array $compiledProduct, \Neo\Models\Product $product) {
        $this->id = $compiledProduct["id"];

        $this->product = $product;

        $this->faces        = $compiledProduct["quantity"];
        $this->spots        = $compiledProduct["spots"];
        $this->impressions  = $compiledProduct["impressions"];
        $this->mediaValue   = $compiledProduct["media_value"];
        $this->unitPrice    = $compiledProduct["unit_price"];
        $this->price        = $compiledProduct["price"];
        $this->isDiscounted = $compiledProduct["isDiscounted"];
        $this->cpm          = $compiledProduct["cpm"];
    }
}
