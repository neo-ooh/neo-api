<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastResource.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;

/**
 * @property int                   $id
 * @property BroadcastResourceType $type
 */
class BroadcastResource extends Model {
    use HasPublicRelations;

    protected $table = "broadcast_resources";

    protected $casts = [
        "type" => BroadcastResourceType::class,
    ];

    protected $fillable = [
        "type",
    ];

    public $timestamps = false;

    protected array $publicRelations = [
        "jobs"            => "jobs",
        "representations" => "external_representations",
        "resource"        => "append:resource",
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return HasMany<BroadcastJob>
     */
    public function jobs(): HasMany {
        return $this->hasMany(BroadcastJob::class, "resource_id", "id")
                    ->orderBy('created_at', 'desc');
    }

    /**
     * @return HasMany<ExternalResource>
     */
    public function external_representations(): HasMany {
        return $this->hasMany(ExternalResource::class, "resource_id", "id")
                    ->orderBy('created_at', 'desc')
                    ->withTrashed();
    }

    public function getResourceAttribute() {
        return match ($this->type) {
            BroadcastResourceType::Creative => Creative::withTrashed()->find($this->getKey())?->toResource(),
            BroadcastResourceType::Content  => Content::withTrashed()->find($this->getKey())?->toResource(),
            BroadcastResourceType::Schedule => Schedule::withTrashed()->find($this->getKey())?->toResource(),
            BroadcastResourceType::Campaign => Campaign::withTrashed()->find($this->getKey())?->toResource(),
            BroadcastResourceType::Tag      => throw new \Exception('Unsupported'),
        };
    }
}
