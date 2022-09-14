<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Creative.php
 */

namespace Neo\Modules\Broadcast\Models;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Media\Video;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Neo\Models\Actor;
use Neo\Modules\Broadcast\Enums\BroadcastResourceType;
use Neo\Modules\Broadcast\Enums\CreativeType;
use Neo\Modules\Broadcast\Models\StructuredColumns\CreativeProperties;
use Neo\Services\Broadcast\Broadcast;
use Ramsey\Collection\Collection;

/**
 * Neo\Modules\Broadcast\Models\Creative
 *
 * @property int                            $id
 * @property CreativeType                   $type
 * @property int                            $owner_id
 * @property int                            $content_id
 * @property int                            $frame_id
 * @property string                         $original_name
 * @property int                            $duration
 * @property CreativeProperties             $properties
 *
 * @property Actor                          $owner
 * @property Content                        $content
 * @property Frame                          $frame
 *
 * @property Collection<CreativeExternalId> $external_ids
 *
 * @property string                         $file_path
 * @property string                         $file_url
 * @property string                         $thumbnail_path
 * @property string                         $thumbnail_url
 *
 * @mixin Builder
 */
class Creative extends BroadcastResourceModel {
    use SoftDeletes;

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
     * @var array
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

    protected $casts = [
        "type"       => CreativeType::class,
        "properties" => CreativeProperties::class,
    ];

    public static function boot(): void {
        parent::boot();

        static::deleting(static function (Creative $creative) {
            // Tell services to disable the creative
            /** @var CreativeExternalId $externalId */
            foreach ($creative->external_ids as $externalId) {
                Broadcast::network($externalId->network_id)->destroyCreative($externalId->external_id);
                $externalId->delete();
            }

            // If the content has no more creatives attached to it, we reset its duration
            // We check for 1 creative and not zero has we are not deleted yet
            if (($creative->content->duration) !== 0.0 && $creative->content->creatives_count === 1) {
                $creative->content->duration = 0;
                $creative->content->save();
            }

            $creative->deleteFile();

            if ($creative->isForceDeleting()) {
                $creative->deleteThumbnail();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    public function content(): BelongsTo {
        return $this->belongsTo(Content::class, 'content_id', 'id');
    }

    public function frame(): BelongsTo {
        return $this->belongsTo(Frame::class, 'frame_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | File & thumbnail accessors
    |--------------------------------------------------------------------------
    */

    /**
     * file_url
     *
     * @return string|null
     */
    public function getFileUrlAttribute(): ?string {
        return Storage::disk("public")->url($this->file_path);
    }

    /**
     * file_path
     *
     * @return string|null
     */
    public function getFilePathAttribute(): ?string {
        return 'creatives/creative_' . $this->getKey() . '.' . $this->properties->extension;
    }

    /**
     * thumbnail_path
     *
     * @return string|null
     */
    public function getThumbnailPathAttribute(): ?string {
        return 'creatives/creative_' . $this->getKey() . "_thumb.jpeg";
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

    /**
     * @throws FileNotFoundException
     */
    public function storeFile(UploadedFile $file): void {
        if (Storage::disk("public")->exists($this->file_path)) {
            Storage::disk("public")->delete($this->file_path);
        }

        $this->createThumbnail($file);
        Storage::disk("public")
               ->putFileAs("creatives", $file, $this->id . '.' . $this->properties->extension, ["visibility" => "public"]);
    }


    /**
     * Create the thumbnail of the creative
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function createThumbnail(UploadedFile $file): bool {
        if ($this->type !== CreativeType::Static) {
            // Thumbnails are only supported for static creatives as for now
            // TODO: Add support for Url creatives' thumbnails
            return false;
        }

        if (Storage::disk("public")->exists($this->thumbnail_path)) {
            Storage::disk("public")->delete($this->thumbnail_path);
        }

        $result = false;

        switch (strtolower($file->extension())) {
            case "jpg":
            case "jpeg":
            case "png":
                $result = $this->createImageThumbnail($file);
                break;
            case "mp4":
                $result = $this->createVideoThumbnail($file);
                break;
        }

        Storage::disk("public")->setVisibility($this->thumbnail_path, 'public');

        return $result;
    }


    /**
     * Create the thumbnail of image creative
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    private function createImageThumbnail(UploadedFile $file): bool {
        $img = Image::make($file);
        $img->resize(1280, 1280, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        return Storage::disk("public")->put($this->thumbnail_path, $img->encode("jpg", 75)->getEncoded());
    }


    /**
     * Create the thumbnail of video creative
     *
     * @param UploadedFile $file
     *
     * @return bool
     */
    private function createVideoThumbnail(UploadedFile $file): bool {
        $ffmpeg = FFMpeg::create(config('ffmpeg'));

        $tempName = 'thumb_' . $this->properties->checksum;
        $tempFile = Storage::disk('local')->path($tempName);

        //thumbnail
        /** @var Video $video */
        $video = $ffmpeg->open($file->path());
        $frame = $video->frame(TimeCode::fromSeconds(1));
        $frame->save($tempFile);
        $result = Storage::disk("public")->writeStream($this->thumbnail_path, Storage::disk('local')->readStream($tempName));

        // Clean temporary file
        Storage::disk('local')->delete($tempName);

        return $result;
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
}
