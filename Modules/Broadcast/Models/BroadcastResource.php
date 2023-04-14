<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BroadcastResource.php
 */

namespace Neo\Modules\Broadcast\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Services\Resources\ExternalBroadcasterResource;

/**
 * @property int                             $id
 * @property BroadcastResourceType           $type
 *
 * @property ExternalBroadcasterResource     $resource
 * @property Collection<BroadcastJob>        $jobs
 * @property Collection<ExternalResource>    $external_representations
 * @property Collection<ResourcePerformance> $performances
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
        "jobs"            => ["jobs.creator", "jobs.updator"],
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

    public function performances() {
        return $this->hasMany(ResourcePerformance::class, "resource_id", "id");
    }

    /**
     * @throws Exception
     */
    public function getResourceAttribute() {
        return match ($this->type) {
            BroadcastResourceType::Creative => Creative::withTrashed()->find($this->getKey())?->toResource(),
            BroadcastResourceType::Content  => Content::withTrashed()->find($this->getKey())?->toResource(),
            BroadcastResourceType::Schedule => Schedule::withTrashed()->find($this->getKey())?->toResource(),
            BroadcastResourceType::Campaign => Campaign::withTrashed()->find($this->getKey())?->toResource(),
            BroadcastResourceType::Tag      => throw new Exception('Unsupported'),
        };
    }
}
