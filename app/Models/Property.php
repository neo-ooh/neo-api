<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Property.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Neo\Models\Odoo\Property as OdooProperty;
use Neo\Rules\AccessibleProperty;

/**
 * Class Property
 *
 * @property int                                   $actor_id
 * @property int                                   $address_id
 * @property int                                   $network_id
 * @property Date                                  $created_at
 * @property Date                                  $updated_at
 *
 * @property Actor                                 $actor
 * @property PropertyTrafficSettings               $traffic
 * @property Address|null                          $address
 * @property PropertyData                          $data
 * @property OdooProperty|null                     $odoo
 * @property Network|null                          $network
 * @property Collection<PropertyPicture>           $pictures
 * @property Collection<PropertyFieldSegmentValue> $fields_values
 * @property Collection<OpeningHours>              $opening_hours
 * @property boolean                               $has_tenants
 * @property Date                                  $last_review_at
 * @property Collection<Brand>                     $tenants
 *
 * @property Collection<Product>                   $products
 *
 * @property array                                 $rolling_weekly_traffic
 *
 */
class Property extends SecuredModel {
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "properties";


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $primaryKey = "actor_id";

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    public $casts = [
        "require_traffic"        => "boolean",
        "traffic_grace_override" => "date"
    ];

    protected $dates = [
        "last_review_at"
    ];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleProperty::class;

    public $with = [
        "actor:id,name"
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function actor(): BelongsTo {
        return $this->belongsTo(Actor::class, "actor_id");
    }

    public function network(): BelongsTo {
        return $this->belongsTo(Network::class, "network_id");
    }

    public function traffic(): HasOne {
        return $this->hasOne(PropertyTrafficSettings::class, "property_id", "actor_id");
    }

    public function address(): BelongsTo {
        return $this->belongsTo(Address::class, "address_id", "id");
    }

    public function odoo(): HasOne {
        return $this->hasOne(OdooProperty::class, "property_id", "actor_id");
    }

    public function data(): HasOne {
        return $this->hasOne(PropertyData::class, "property_id", "actor_id");
    }

    public function pictures(): HasMany {
        return $this->hasMany(PropertyPicture::class, "property_id", "actor_id")->orderBy("order");
    }

    public function fields_values(): HasMany {
        return $this->hasMany(PropertyFieldSegmentValue::class, "property_id", "actor_id");
    }

    public function products(): HasMany {
        return $this->hasMany(Product::class, "property_id", "actor_id");
    }

    public function products_categories(): BelongsToMany {
        return $this->belongsToMany(ProductCategory::class, "products", "property_id", "category_id")
                    ->distinct();
    }

    public function opening_hours(): HasMany {
        return $this->hasMany(OpeningHours::class, "property_id")->orderBy("weekday");
    }

    public function tenants(): BelongsToMany {
        return $this->belongsToMany(Brand::class, "properties_tenants", "property_id", "brand_id");
    }

    public function demographicValues(): HasMany {
        return $this->hasMany(DemographicValue::class, "property_id", "actor_id");
    }


    /*
    |--------------------------------------------------------------------------
    | Misc
    |--------------------------------------------------------------------------
    */

    public function getTraffic(int $year, int $month): int|null {
        /** @var ?PropertyTrafficMonthly $traffic */
        $traffic = $this->traffic->data
            ->where("year", "=", $year)
            ->where("month", "=", $month)
            ->first();

        if (!$traffic) {
            return null;
        }

        return $traffic->final_traffic;
    }
}
