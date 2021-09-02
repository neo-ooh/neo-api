<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ProductCategory.php
 */

namespace Neo\Models\Odoo;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Neo\Models\Location;

/**
 * @property int $id
 * @property int $odoo_id
 * @property int $product_type_id
 * @property string $name
 * @property string $internal_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Collection<Property> $odoo_properties
 * @property ProductType $product_type
 * @property Collection<Location> $locations
 */
class ProductCategory extends Model {
    protected $table = "odoo_products_categories";

    protected $fillable = [
        "odoo_id",
        "product_type_id",
        "name",
        "internal_name",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function odoo_properties() {
        return $this->belongsToMany(Property::class, "odoo_properties_products_categories", "product_category_id", "property_id");
    }

    public function product_type() {
        return $this->belongsTo(ProductType::class, "product_type_id", "id");
    }

    public function locations() {
        return $this->belongsToMany(Location::class, "odoo_products_locations", "product_id", "location_id");
    }
}
