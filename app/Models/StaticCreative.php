<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StaticCreative.php
 */

namespace Neo\Models;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Class StaticCreative
 *
 * @package Neo\Models
 *
 * @property int      $creative_id
 * @property string   $extension
 * @property string   $checksum
 *
 * @property string   $file_url
 * @property string   $file_path
 * @property string   $thumbnail_url
 * @property string   $thumbnail_path
 *
 * @property Creative $creative
 */
class StaticCreative extends Model {
    use HasFactory;

    protected $table = "static_creatives";

    protected $primaryKey = "creative_id";

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ["creative_id", "extension", "checksum"];

    protected $appends = ["file_url", "thumbnail_url"];

    /*
    |--------------------------------------------------------------------------
    | Base
    |--------------------------------------------------------------------------
    */

    public function creative(): MorphOne {
        return $this->morphOne("creatives", "properties", "type", "id", "creative_id");
    }

    /*
    |--------------------------------------------------------------------------
    | Creative file access
    |--------------------------------------------------------------------------
    */

    public function getFileUrlAttribute(): ?string {
        return Storage::disk("public")->url($this->file_path);
    }

    public function getFilePathAttribute(): ?string {
        return 'creatives/' . $this->creative_id . '.' . $this->extension;
    }

    public function getThumbnailUrlAttribute(): ?string {
        return Storage::disk("public")->url($this->thumbnail_path);
    }

    public function getThumbnailPathAttribute(): ?string {
        return 'creatives/' . $this->creative_id . '_thumb.jpeg';
    }

    /*
    |--------------------------------------------------------------------------
    | Store file
    |--------------------------------------------------------------------------
    */

    /**
     * @throws FileNotFoundException
     */
    public function store(UploadedFile $file): void {
        if (Storage::disk("public")->exists($this->file_path)) {
            Storage::disk("public")->delete($this->file_path);
        }

        $this->createThumbnail($file);
        Storage::disk("public")
               ->putFileAs("creatives", $file, $this->creative_id . '.' . $this->extension, ["visibility" => "public"]);
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
    public function createThumbnail(UploadedFile $file): bool {
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
     * @return void
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
     * @return void
     * @throws FileNotFoundException
     */
    private function createVideoThumbnail(UploadedFile $file): bool {
        $ffmpeg = FFMpeg::create(config('ffmpeg'));

        $tempName = 'thumb_' . $this->checksum;
        $tempFile = Storage::disk('local')->path($tempName);

        //thumbnail
        $video = $ffmpeg->open($file->path());
        $frame = $video->frame(TimeCode::fromSeconds(1));
        $frame->save($tempFile);
        $result = Storage::disk("public")->writeStream($this->thumbnail_path, Storage::disk('local')->readStream($tempName));

        // Clean temporary file
        Storage::disk('local')->delete($tempName);

        return $result;
    }


}
