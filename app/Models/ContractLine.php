<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ContractLine.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Neo\Models\Traits\HasCompositePrimaryKey;

/**
 * @property-read int $product_id
 * @property-read int $flight_id
 * @property-read int $external_id
 * @property int      $spots
 * @property double   $media_value
 * @property double   $discount
 * @property string   $discount_type
 * @property double   $price
 * @property int      $traffic
 * @property int      $impressions
 */
class ContractLine extends Model {
    use HasCompositePrimaryKey;

    protected $table = "contracts_lines";

    protected $primaryKey = ["product_id", "flight_id"];

    protected $fillable = [
        "product_id",
        "flight_id",
        "external_id",
        "spots",
        "media_value",
        "discount",
        "discount_type",
        "price",
        "traffic",
        "impressions",
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo {
        return $this->belongsTo(Product::class, "product_id", "id");
    }
}
