<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Attachment.php
 */

namespace Neo\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read int $id
 * @property string   $locale
 * @property string   $name
 * @property string   $filename
 * @property Date     $created_at
 * @property Date     $updated_at
 *
 * @property string   $file_path
 * @property string   $url
 */
class Attachment extends Model {
    protected $table = "attachments";

    protected $fillable = [
        "locale",
        "name",
        "filename",
    ];

    protected $appends = [
        "url",
    ];

    public static function boot() {
        parent::boot();

        static::deleting(static function (Attachment $screenshot) {
            Storage::disk("public")->delete($screenshot->file_path);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Screenshot
    |--------------------------------------------------------------------------
    */

    public function getFilePathAttribute() {
        return "attachments/" . Hashids::encode($this->id) . "/" . $this->filename;
    }

    /**
     * @param UploadedFile $file
     * @return false|string
     */
    public function store(UploadedFile $file) {
        return Storage::disk("public")
                      ->putFileAs("attachments/" . Hashids::encode($this->id), $file, $this->filename, ["visibility" => "public"]);
    }

    public function getUrlAttribute() {
        return Storage::disk("public")->url($this->file_path);
    }
}
