<?php

namespace Neo\Models;

use Carbon\Carbon as Date;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Class ActorLogo
 *
 * @package Neo\Models
 *
 * @property int    id
 * @property string original_name
 * @property Date   created_at
 * @property Date   updated_at
 */
class ActorLogo extends Model {
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
    protected $table = "actors_logo";

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['file_path'];


    /*
    |--------------------------------------------------------------------------
    | Custom Attributes
    |--------------------------------------------------------------------------
    */

    public function getFilePathAttribute(): string {
        return Storage::url("actors_logo/{$this->id}.png");
    }

    /**
     * @param UploadedFile $file
     */
    public function store(UploadedFile $file): void {
        Storage::put("actors_logo/{$this->id}.png", Image::make($file)->encode("png"), 'public');
    }

    /**
     * @throws Exception
     */
    public function erase(): void {
        // Delete the file
        Storage::delete($this->file_path);
        $this->delete();
    }
}
