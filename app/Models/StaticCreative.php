<?php

namespace Neo\Models;

use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Image;
use Spatie\Image\Manipulations;
use Spatie\TemporaryDirectory\TemporaryDirectory;

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
        return Storage::url($this->file_path);
    }

    public function getFilePathAttribute(): ?string {
        return 'creatives/' . $this->creative_id . '.' . $this->extension;
    }

    public function getThumbnailUrlAttribute(): ?string {
        return Storage::url($this->thumbnail_path);
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
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }

        $this->createThumbnail($file);
        $file->storePubliclyAs('creatives/', $this->creative_id . '.' . $this->extension);
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

        switch (strtolower($file->extension())) {
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
        $tmp = (new TemporaryDirectory())->create();

        Image::load($file->path())
             ->width(1280)
             ->height(1280)
             ->format(Manipulations::FORMAT_JPG)
             ->quality(75)
             ->save($tmp->path("thumb.jpeg"));

        Storage::put($this->thumbnail_path, $tmp->path("thumb.jpeg"));

        $tmp->delete();
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
