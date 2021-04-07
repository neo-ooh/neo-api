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
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Neo\BroadSign\Jobs\Creatives\DisableBroadSignCreative;
use Neo\Models\Factories\CreativeFactory;

/**
 * Neo\Models\Branding
 *
 * @property int     id
 * @property int     broadsign_ad_copy_id
 * @property int     owner_id
 * @property int     content_id
 * @property int     frame_id
 * @property string  extension
 * @property string  original_name
 * @property string  status
 * @property string  checksum
 * @property int     duration
 *
 * @property Actor   owner
 * @property Content content
 * @property Frame   frame
 *
 * @property string  file_url
 * @property string  file_path
 * @property string  thumbnail_url
 * @property string  thumbnail_path
 *
 * @mixin Builder
 */
class Creative extends Model {
    use HasFactory;
    use SoftDeletes;

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
     * The attributes that should always be loaded.
     *
     * @var array
     */
    protected $appends = ["file_url", "thumbnail_url"];

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

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /* Direct */

    public function eraseThumbnail(): void {
        Storage::delete($this->thumbnail_path);
    }

    protected static function newFactory(): CreativeFactory {
        return CreativeFactory::new();
    }

    public function owner(): BelongsTo {
        return $this->belongsTo(Actor::class, 'owner_id', 'id');
    }

    public function content(): BelongsTo {
        return $this->belongsTo(Content::class, 'content_id', 'id');
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

    public function frame(): BelongsTo {
        return $this->belongsTo(Frame::class, 'frame_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | Custom mechanisms
    |--------------------------------------------------------------------------
    */

    public function getFileUrlAttribute(): string {
        return Storage::url($this->file_path);
    }

    public function getFilePathAttribute(): string {
        return 'creatives/' . $this->id . '.' . $this->extension;
    }

    public function getThumbnailUrlAttribute(): string {
        return Storage::url($this->thumbnail_path);
    }

    public function getThumbnailPathAttribute(): string {
        return 'creatives/' . $this->id . '_thumb.jpeg';
    }

    public function store(UploadedFile $file): void {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }

        $file->storePubliclyAs('creatives/', $this->id . '.' . $this->extension);
        $this->createThumbnail($file);
    }


    /*
    |--------------------------------------------------------------------------
    | Thumbnails
    |--------------------------------------------------------------------------
    */


    /**
     * Create the thumbnail of the creative
     *
     * @param UploadedFile $file
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function createThumbnail(UploadedFile $file): void {
        if (Storage::exists($this->thumbnail_path)) {
            Storage::delete($this->thumbnail_path);
        }

        switch (strtolower($file->extension)) {
            case "jpg":
            case "jpeg":
            case "png":
                $this->createImageThumbnail($file);
                break;
            case "mp4":
                $this->createVideoThumbnail($file);
                break;
        }

        Storage::setVisibility($this->thumbnail_path, 'public');
    }


    /**
     * Create the thumbnail of image creative
     *
     * @param UploadedFile $file
     *
     * @return void
     */
    private function createImageThumbnail(UploadedFile $file): void {
        $img = Image::make($file->path());
        $img->resize(1280, 1280, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        Storage::put($this->thumbnail_path, $img->stream("jpg", 75));
    }


    /**
     * Create the thumbnail of video creative
     *
     * @param UploadedFile $file
     *
     * @return void
     * @throws FileNotFoundException
     */
    private function createVideoThumbnail(UploadedFile $file): void {
        $ffmpeg = FFMpeg::create(config('ffmpeg'));

        $tempName = 'thumb_' . $this->checksum;
        $tempFile = Storage::disk('local')->path($tempName);

        //thumbnail
        $video = $ffmpeg->open($file->path());
        $frame = $video->frame(TimeCode::fromSeconds(1));
        $frame->save($tempFile);
        Storage::writeStream($this->thumbnail_path, Storage::disk('local')->readStream($tempName));
        Storage::disk('local')->delete($tempName);
    }
}
