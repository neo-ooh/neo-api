<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Phone.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * Class Phone
 *
 * @property int         $id
 * @property string      $number_country
 * @property PhoneNumber $number
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class Phone extends Model {
    protected $table = "phones";

    protected $fillable = [
        "number_country",
        "number",
    ];

    protected $casts = [
        "number" => E164PhoneNumberCast::class,
    ];

    protected $appends = [
        "formatted_number"
    ];

    public function getFormattedNumberAttribute() {
        return $this->number->formatNational();
    }
}
