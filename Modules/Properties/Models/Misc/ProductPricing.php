<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductPricing.php
 */

namespace Neo\Modules\Properties\Models\Misc;

use Neo\Modules\Properties\Enums\PriceType;
use Neo\Modules\Properties\Models\Pricelist;
use Neo\Modules\Properties\Models\PricelistProduct;
use Neo\Modules\Properties\Models\PricelistProductsCategory;
use Neo\Modules\Properties\Models\Product;

class ProductPricing {
    protected function __construct(
        protected PriceType $price_type,
        protected float     $price
    ) {
    }

    public static function make(Product $product) {
        /** @var Pricelist|null $pricelist */
        $pricelist = $product->pricelist;

        if (!$pricelist) {
            return new static(PriceType::Unit, $product->unit_price);
        }

        /** @var PricelistProduct|PricelistProductsCategory|null $pricing */
        $pricing = ($pricelist->products()->firstWhere("id", "=", $product->getKey())
            ?? $pricelist->categories()->firstWhere("id", "=", $product->category_id))
            ?->pricing;

        if (!$pricing) {
            return new static(PriceType::Unit, $product->unit_price);
        }

        return match ($pricing->pricing) {
            PriceType::Unit        => new static(PriceType::Unit, $pricing->value),
            PriceType::UnitProduct => new static(PriceType::UnitProduct, $pricing->value),
            PriceType::CPM         => new static(PriceType::CPM, $pricing->value),
        };
    }

    public function getType(): PriceType {
        return $this->price_type;
    }

    public function getPrice(): float {
        return $this->price;
    }
}
