<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Address.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Grimzy\LaravelMysqlSpatial\Eloquent\SpatialTrait;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Address
 *
 * @package Neo\Models
 * @property int        $id
 * @property string     $line_1
 * @property string     $line_2
 * @property int        $city_id
 * @property City       $city
 * @property string     $zipcode
 * @property Point|null $geolocation
 * @property Date       $created_at
 * @property Date       $updated_at
 *
 * @property string     $string_representation Human-readable version of the address
 */
class Address extends Model {
    use SpatialTrait;

    public bool $wktOptions = false;

    protected $table = "addresses";

    protected $primaryKey = "id";

    protected array $spatialFields = [
        "geolocation",
    ];

    protected $with = [
        "city",
        "city.province",
        "city.province.country",
        "city.market",
    ];

    protected $appends = [
        "string_representation",
    ];

    public function city() {
        return $this->belongsTo(City::class, "city_id");
    }

    public function getStringRepresentationAttribute(): string {
        $str = $this->line_1;
        if ($this->line_2 && $this->line_2 !== '') {
            $str .= ", $this->line_2";
        }

        $zipcode = substr($this->zipcode, 0, 3) . " " . substr($this->zipcode, 3);

        $str .= ", {$this->city->name} {$this->city->province->slug} $zipcode";
        $str .= ", {$this->city->province->country->name}";

        return $str;
    }
}
