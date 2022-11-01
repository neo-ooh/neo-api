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
use Neo\Modules\Broadcast\Enums\ExternalResourceType;
use Neo\Modules\Broadcast\Models\StructuredColumns\ExternalResourceData;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResourceId;
use Spatie\DataTransferObject\Exceptions\UnknownProperties;

/**
 * @property int                  $id
 * @property ExternalResourceType $type
 * @property int                  $resource_id
 * @property int                  $broadcaster_id
 * @property ExternalResourceData $data
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 * @property Carbon|null          $deleted_at
 */
class ExternalResource extends Model {
    use SoftDeletes;

    protected $table = "external_resources";

    protected $casts = [
        "type" => ExternalResourceType::class,
        "data" => ExternalResourceData::class,
    ];

    protected $fillable = [
        "resource_id",
        "broadcaster_id",
        "type",
        "data",
        "created_at",
        "updated_at",
    ];

    /**
     * @return ExternalBroadcasterResourceId
     * @throws UnknownProperties
     */
    public function toResource(): ExternalBroadcasterResourceId {
        return new ExternalBroadcasterResourceId([
            "type"           => $this->type,
            "broadcaster_id" => $this->broadcaster_id,
            "external_id"    => $this->data->external_id,
        ]);
    }
}
