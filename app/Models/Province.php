<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Province.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Province
 *
 * @package Neo\Models
 * @property string  $slug
 * @property int     $country_id
 * @property string  $name
 *
 * @property Country $country
 *
 * @property int     $id
 */
class Province extends Model {
    protected $table = "provinces";

    protected $primaryKey = "id";

    public $timestamps = false;

    protected $fillable = [
        "slug",
        "country_id",
        "name",
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName() {
        return 'slug';
    }

    public function country() {
        return $this->belongsTo(Country::class, "country_id");
    }

    public function markets() {
        return $this->hasMany(Market::class, "province_id")->orderBy("name_en");
    }

    public function cities() {
        return $this->hasMany(City::class, "province_id")->orderBy("name");
    }
}
