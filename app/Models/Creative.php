<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Creative.php
 */

namespace Neo\Models;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Neo\BroadSign\Jobs\Creatives\DisableBroadSignCreative;
use Neo\Models\Factories\CreativeFactory;

/**
 * Neo\Models\Branding
 *
 * @property int                            $id
 * @property int                            $type
 * @property int                            $broadsign_ad_copy_id
 * @property int                            $owner_id
 * @property int                            $content_id
 * @property int                            $frame_id
 * @property string                         $original_name
 * @property string                         $status
 * @property int                            $duration
 *
 * @property Actor                          $owner
 * @property Content                        $content
 * @property Frame                          $frame
 *
 * @property DynamicCreative|StaticCreative $properties
 *
 * @mixin Builder
 */
class Creative extends Model {
    use HasFactory;
    use SoftDeletes;

    const TYPE_STATIC = "static";
    const TYPE_DYNAMIC = "dynamic";

    /*
    |--------------------------------------------------------------------------
    | Table properties
    |--------------------------------------------------------------------------
    */


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'creatives';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'owner_id',
        'content_id',
        'frame_id',
        'extension',
        'status',
        'checksum',
    ];

    /**
     * The relations that should always be loaded
     *
     * @var array
     */
    protected $with = ["properties"];

    public static function boot(): void {
        parent::boot();

        static::deleting(function (Creative $creative) {
            // Disabled the creative in Broadsign
            if ($creative->broadsign_ad_copy_id !== null) {
                DisableBroadSignCreative::dispatch($creative->broadsign_ad_copy_id);
            }

            // If the content has no more creatives attached to it, we reset its duration
            // We check for 1 creative and not zero has we are not deleted yet
            if ($creative->content->duration !== 0 && $creative->content->creatives_count === 1) {
                $creative->content->duration = 0;
                $creative->content->save();
            }

            $creative->eraseFile();
            if ($creative->isForceDeleting()) {
                $creative->eraseThumbnail();
            }
        });
    }

    public function eraseFile(): void {
        Storage::delete($this->file_path);
    }

    public function eraseThumbnail(): void {
        Storage::delete($this->thumbnail_path);
    }

    protected static function newFactory(): CreativeFactory {
        return CreativeFactory::new();
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function properties(): MorphTo {
        return $this->morphTo("properties", "type", "id");
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    public function content(): BelongsTo {
        return $this->belongsTo(Content::class, 'content_id', 'id');
    }

    public function frame(): BelongsTo {
        return $this->belongsTo(Frame::class, 'frame_id', 'id');
    }
}
