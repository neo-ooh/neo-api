<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Creative.php
 */

namespace Neo\Modules\Broadcast\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Neo\Models\Actor;
use Neo\Models\Traits\HasPublicRelations;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\CreativeType;
use Neo\Modules\Broadcast\Exceptions\UnsupportedFileFormatException;
use Neo\Modules\Broadcast\Jobs\Creatives\DeleteCreativeJob;
use Neo\Modules\Broadcast\Models\StructuredColumns\CreativeProperties;
use Neo\Modules\Broadcast\Rules\AccessibleCreative;
use Neo\Modules\Broadcast\Services\Resources\Creative as CreativeResource;
use Neo\Modules\Broadcast\Utils\ThumbnailCreator;
use Vinkla\Hashids\Facades\Hashids;

/**
 * Neo\Modules\Broadcast\Models\Creative
 *
 * @property int                $id
 * @property CreativeType       $type
 * @property int                $owner_id
 * @property int                $content_id
 * @property int                $frame_id
 * @property string             $original_name
 * @property double             $duration
 * @property CreativeProperties $properties
 *
 * @property Actor              $owner
 * @property Content            $content
 * @property Frame              $frame
 *
 * @property string             $file_uid
 * @property string             $file_path
 * @property string             $file_url
 * @property string             $thumbnail_path
 * @property string             $thumbnail_url
 *
 * @mixin Builder
 */
class Creative extends BroadcastResourceModel {
    use SoftDeletes;
    use HasPublicRelations;

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */

    public BroadcastResourceType $resourceType = BroadcastResourceType::Creative;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "creatives";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        "type",
        "owner_id",
        "content_id",
        "frame_id",
        "original_name",
        "duration",
        "properties",
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        "type"       => CreativeType::class,
        "properties" => CreativeProperties::class,
    ];

    protected $appends = [
        "thumbnail_url",
        "file_url",
    ];

    protected array $publicRelations = [];

    /**
     * The rule used to validate access to the model upon binding it with a route
     *
     * @var string
     */
    protected string $accessRule = AccessibleCreative::class;

    public static function boot(): void {
        parent::boot();

        static::deleting(static function (Creative $creative) {
            // If the content has no more creatives attached to it, we reset its duration
            // We check for 1 creative and not zero has we are not deleted yet
            if ($creative->content->creatives_count === 1) {
                $creative->content->duration = 0;
                $creative->content->save();
            }

            $creative->deleteFile();

            if ($creative->isForceDeleting()) {
                // Tell services to disable the creative
                DeleteCreativeJob::dispatch($creative->getKey());

                $creative->deleteThumbnail();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * @return BelongsTo<Actor, Creative>
     */
    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id')->withTrashed();
    }

    /**
     * @return BelongsTo<Content, Creative>
     */
    public function content(): BelongsTo {
        return $this->belongsTo(Content::class, 'content_id', 'id')->withTrashed();
    }

    /**
     * @return BelongsTo<Frame, Creative>
     */
    public function frame(): BelongsTo {
        return $this->belongsTo(Frame::class, 'frame_id', 'id')->withTrashed();
    }

    /*
    |--------------------------------------------------------------------------
    | File & thumbnail accessors
    |--------------------------------------------------------------------------
    */

    public function getFileUidAttribute() {
        return Hashids::encode($this->getKey());
    }

    /**
     * file_path
     *
     * @return string|null
     */
    public function getFilePathAttribute(): ?string {
        if ($this->type === CreativeType::Static) {
            return 'creatives/' . $this->file_uid . '.' . $this->properties->extension;
        }

        return null;
    }

    /**
     * file_url
     *
     * @return string|null
     */
    public function getFileUrlAttribute(): ?string {
        if ($this->type === CreativeType::Static) {
            return Storage::disk("public")->url($this->file_path);
        }

        return $this->properties->url;
    }

    /**
     * thumbnail_path
     *
     * @return string|null
     */
    public function getThumbnailPathAttribute(): ?string {
        return 'creatives/' . $this->file_uid . "_thumb.jpeg";
    }

    /**
     * thumbnail_url
     *
     * @return string|null
     */
    public function getThumbnailUrlAttribute(): ?string {
        return Storage::disk("public")->url($this->thumbnail_path);
    }

    /*
    |--------------------------------------------------------------------------
    | File & thumbnail handles
    |--------------------------------------------------------------------------
    */

    public function storeFile(UploadedFile $file): void {
        if (Storage::disk("public")->exists($this->file_path)) {
            Storage::disk("public")->delete($this->file_path);
        }

        $this->createThumbnail($file);

        Storage::disk("public")
               ->putFileAs("creatives", $file, $this->file_uid . '.' . $this->properties->extension, ["visibility" => "public"]);
    }


    /**
     * Create the thumbnail of the creative
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    protected function createThumbnail(UploadedFile $file): bool {
        if ($this->type !== CreativeType::Static) {
            // Thumbnails are only supported for static creatives as for now
            // TODO: Add support for Url creatives' thumbnails
            return false;
        }

        if (Storage::disk("public")->exists($this->thumbnail_path)) {
            Storage::disk("public")->delete($this->thumbnail_path);
        }

        $creator = new ThumbnailCreator($file);
        try {
            Storage::disk("public")
                   ->writeStream($this->thumbnail_path, $creator->getThumbnailAsStream(), ["visibility" => "public"]);

            return true;
        } catch (UnsupportedFileFormatException) {
            return false;
        }
    }

    public function deleteFile(): void {
        if ($this->type === CreativeType::Static) {
            Storage::disk("public")->delete($this->file_path);
        }
    }

    public function deleteThumbnail(): void {
        if ($this->type === CreativeType::Static) {
            Storage::disk("public")->delete($this->thumbnail_path);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    */

    /**
     * @param int|null $broadcasterId
     * @return CreativeResource
     */
    public function toResource(int|null $broadcasterId = null): CreativeResource {
        return new CreativeResource(
            id                  : $this->getKey(),
            advertiser          : $broadcasterId ? $this->content->library->advertiser?->getExternalRepresentation($broadcasterId) : null,
            name                : $this->owner->name . " - " . $this->original_name . " #" . $this->getKey(),
            fileName            : $this->original_name,
            type                : $this->type,
            width               : $this->frame->width,
            height              : $this->frame->height,
            length_ms           : $this->duration * 1000,
            path                : $this->type === CreativeType::Static ? $this->file_path : "",
            extension           : $this->type === CreativeType::Static ? $this->properties->extension : "",
            url                 : $this->type === CreativeType::Static ? $this->file_url : $this->properties->url,
            refresh_rate_minutes: $this->type === CreativeType::Static ? 0 : $this->properties->refresh_interval_minutes,
        );
    }

    /**
     * Get the external resource matching the given parameters
     *
     * @param int $broadcasterId
     * @return ExternalResource|null
     */
    public function getExternalRepresentation(int $broadcasterId): ExternalResource|null {
        return $this->external_representations->where("broadcaster_id", "=", $broadcasterId)->first();
    }
}
