<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - BrandingFile.php
 */

namespace Neo\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * Class BrandingFile
 *
 * @package Neo\Models
 *
 * @property int    branding_id
 * @property string filename
 *
 * @property string filePath
 *
 * @mixin Builder
 */
class BrandingFile extends Model {
    use Notifiable;
    use HasFactory;

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
    protected $table = 'brandings_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "type",
        "branding_id",
        "filename",
        "original_name",
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['file_path'];

    protected static function newFactory(): Factories\BrandingFileFactory {
        return Factories\BrandingFileFactory::new();
    }


    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function branding(): BelongsTo {
        return $this->belongsTo(Branding::class, 'branding_id', 'id');
    }


    /*
    |--------------------------------------------------------------------------
    | Custom Attributes
    |--------------------------------------------------------------------------
    */

    public function getFilePathAttribute(): string {
        return Storage::disk("public")->url('brandings/' . $this->branding_id . '/' . $this->filename);
    }

    /*
    |--------------------------------------------------------------------------
    | Custom mechanisms
    |--------------------------------------------------------------------------
    */

    /**
     * @param UploadedFile $file
     */
    public function store(UploadedFile $file): void {
        Storage::disk("public")
               ->putFileAs("/brandings", $file, $this->branding_id . '/' . $this->filename, ["visibility" => "public"]);
    }

    /**
     * @throws Exception
     */
    public function erase(): void {
        // Delete the file
        Storage::disk("public")->delete($this->filePath);
        $this->delete();
    }
}
