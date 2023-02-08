<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LoopConfiguration.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;

/**
 * @property int                         $id
 * @property string                      $name
 * @property int                         $loop_length_ms
 * @property int                         $spot_length_ms
 * @property int                         $reserved_spots
 * @property Carbon                      $start_date
 * @property Carbon                      $end_date
 * @property int                         $max_spots_count
 * @property int                         $free_spots_count
 *
 * @property Collection<ProductCategory> $product_categories
 * @property Collection<Product>         $products
 *
 * @mixin Builder<LoopConfiguration>
 */
class LoopConfiguration extends Model {
    protected $table = "loop_configurations";

    protected $primaryKey = "id";

    protected $casts = [
        "start_date" => "date:Y-m-d",
        "end_date"   => "date:Y-m-d",
    ];

    protected $fillable = [
        "name",
        "loop_length_ms",
        "spot_length_ms",
        "reserved_spots",
        "start_date",
        "end_date",
    ];

    public function formats(): BelongsToMany {
        return $this->belongsToMany(ProductCategory::class, "products_categories_loop_configurations", "loop_configuration_id", "product_category_id");
    }

    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, "products_loop_configurations", "loop_configuration_id", "product_id");
    }

    /**
     * @return bool Tell if the loop configuration dates makes it crosses the new year
     */
    public function crossesNewYear(): bool {
        return $this->start_date->isAfter($this->end_date);
    }

    /**
     * @param Carbon $date
     * @return bool True if the given date fall inside the loop configuration period
     */
    public function dateIsInPeriod(Carbon $date) {
        $normalizedDate = $date->clone()->setYear(2000);

        // If the period crosses the new year, we have to change our comparison
        if ($this->crossesNewYear()) {
            // ----x----|start|----✓----|NY|----✓----|end|----x----
            return $normalizedDate >= $this->start_date || $normalizedDate <= $this->end_date;
        }

        // ----x----|start|----✓----|end|----x----|NY|
        return $this->start_date <= $normalizedDate && $normalizedDate <= $this->end_date;
    }

    /**
     * Tell how many spots there is in the loop, including reserved spots
     *
     * @return positive-int
     */
    public function getSpotCount(): int {
        return $this->loop_length_ms / $this->spot_length_ms;
    }
}
