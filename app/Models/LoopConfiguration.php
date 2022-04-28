<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - LoopConfiguration.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
 */
class LoopConfiguration extends Model {
    protected $table = "loop_configurations";

    protected $primaryKey = "id";

    protected $dates = [
        "start_date",
        "end_date"
    ];

    protected $fillable = [
        "name",
        "loop_length_ms",
        "spot_length_ms",
        "reserved_spots",
        "start_date",
        "end_date"
    ];

    public function product_categories(): BelongsToMany {
        return $this->belongsToMany(ProductCategory::class, "products_categories_loop_configurations", "loop_configuration_id", "product_category_id");
    }

    public function products(): BelongsToMany {
        return $this->belongsToMany(Product::class, "products_loop_configurations", "loop_configuration_id", "product_id");
    }
}
