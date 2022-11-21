<?php
/*
 * Copyright 2022 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - AdvertiserRepresentation.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Neo\Models\Traits\HasCompositePrimaryKey;

/**
 * @property int    $advertiser_id
 * @property int    $broadcaster_id
 * @property string $external_id
 *
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class AdvertiserRepresentation extends Model {
    use HasCompositePrimaryKey;

    protected $table = "advertiser_representations";

    public $incrementing = false;

    protected $primaryKey = ["advertiser_id", "broadcaster_id"];

    protected $fillable = [
        "advertiser_id",
        "broadcaster_id",
        "external_id",
    ];
}
