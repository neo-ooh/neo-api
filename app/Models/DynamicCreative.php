<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - DynamicCreative.php
 */

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class DynamicCreative
 *
 * @package Neo\Models
 *
 * @property int    creative_id
 * @property string url
 * @property int    refresh_interval minutes
 * @property string $thumbnail_path
 */
class DynamicCreative extends Model {
    use HasFactory;

    protected $table = "dynamic_creatives";

    protected $primaryKey = "creative_id";

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ["creative_id", "url", "refresh_interval"];

    protected $appends = ["thumbnail_url"];

    /**
     * thumbnail_url
     *
     * @return string|null
     */
    public function getThumbnailUrlAttribute(): ?string {
        return Storage::disk("public")->url($this->thumbnail_path);
    }
}
