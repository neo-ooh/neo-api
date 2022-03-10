<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - CreativeExternalId.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CreativeExternalId
 *
 * @package Neo\Models
 *
 * @property int $creative_id
 * @property int $network_id
 * @property string $external_id
 * @property Date $created_at
 * @property Date $updated_at
 */
class CreativeExternalId extends Model
{
    use HasFactory;

    protected $table = "creatives_external_ids";

    protected $primaryKey = "creative_id";

    public $incrementing = false;

    protected $fillable = [
        "creative_id",
        "network_id",
        "external_id",
    ];

    public function creative() {
        return $this->belongsTo(Creative::class, "creative_id");
    }
}
