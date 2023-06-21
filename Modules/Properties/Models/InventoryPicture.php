<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - InventoryPicture.php
 */

namespace Neo\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property int           $id
 * @property string        $name
 * @property int|null      $property_id
 * @property int|null      $product_id
 * @property int           $width
 * @property int           $height
 * @property int           $order
 * @property int           $extension
 * @property string        $description
 *
 * @property Property|null $property
 * @property Product|null  $product
 *
 * @property string        $uid
 * @property string        $file_path
 * @property string        $url
 *
 */
class InventoryPicture extends Model {
    protected $table = "inventory_pictures";

    protected $primaryKey = "id";

    protected $fillable = [
        "name",
        "property_id",
        "product_id",
        "width",
        "height",
        "order",
        "extension",
    ];

    protected $appends = [
        "url",
    ];

    public static function boot() {
        parent::boot();

        static::deleting(static function (InventoryPicture $picture) {
            Storage::disk("public")->delete($picture->file_path);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function property() {
        return $this->belongsTo(Property::class, "property_id", "actor_id");
    }

    public function product() {
        return $this->belongsTo(Product::class, "product_id", "id");
    }

    /*
    |--------------------------------------------------------------------------
    | Attributes
    |--------------------------------------------------------------------------
    */

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
