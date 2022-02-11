<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyPicture.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property int      $id
 * @property string   $extension
 * @property string   $name
 * @property int      $property_id
 * @property int      $width
 * @property int      $height
 * @property int      $order
 *
 * @property Property $property
 *
 * @property string   $uid
 * @property string   $file_path
 * @property string   $url
 *
 */
class PropertyPicture extends Model {
    protected $table = "properties_pictures";

    protected $primaryKey = "id";

    protected $fillable = [
        "extension",
        "name",
        "property_id",
        "width",
        "height",
        "order"
    ];

    protected $appends = [
        "url"
    ];

    public static function boot() {
        parent::boot();

        static::deleting(static function (PropertyPicture $picture) {
            Storage::disk("public")->delete($picture->file_path);
        });
    }

    public function property() {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }

    public function getUidAttribute() {
        return Hashids::encode($this->id);
    }

    public function getFilePathAttribute() {
        return "properties/pictures/$this->uid.$this->extension";
    }

    public function getUrlAttribute() {
        return Storage::disk("public")->url($this->file_path);
    }
}
