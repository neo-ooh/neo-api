<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductCategory.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int                  $id
 * @property int                  $type_id
 * @property string               $name_en
 * @property string               $name_fr
 * @property string               $fill_strategy
 * @property int                  $external_id
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 *
 * @property Collection<Property> $odoo_properties
 * @property ProductType          $type
 */
class ProductCategory extends Model {
    protected $table = "products_categories";

    protected $fillable = [
        "type_id",
        "name_en",
        "name_fr",
        "fill_strategy",
        "external_id",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function properties(): BelongsToMany {
        return $this->belongsToMany(Property::class, "products", "category_id", "property_id");
    }

    public function product_type(): BelongsTo {
        return $this->belongsTo(ProductType::class, "type_id", "id");
    }

    public function products(): HasMany {
        return $this->hasMany(Product::class, "category_id", "id");
    }
}
