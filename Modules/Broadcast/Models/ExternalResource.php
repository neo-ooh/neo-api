<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ExternalResource.php
 */

namespace Neo\Modules\Broadcast\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;

/**
 * @property int                  $id
 * @property int                  $resource_id
 * @property int                  $broadcaster_id
 * @property ExternalResourceData $data
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon               $deleted_at
 */
class ExternalResource extends Model {
    use SoftDeletes;

    protected $table = "external_resources";

    protected $casts = [
        "data" => "array",
    ];

    protected $fillable = [
        "resource_id",
        "broadcaster_id",
        "type",
        "data",
        "created_at",
        "updated_at",
    ];
}
