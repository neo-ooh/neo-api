<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - NewsRecord.php
 */

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class NewsRecord
 *
 * @package Neo\Models
 *
 * @property int    $id
 * @property string $cp_id
 * @property string $subject
 * @property string $locale
 * @property string $headline
 * @property Date   $date
 * @property string $media
 * @property int    $media_width
 * @property int    $media_height
 * @property Date   $created_at
 * @property Date   $updated_at
 */
class NewsRecord extends Model {

    protected $table = "news_records";

    protected $dates = ["date"];

    protected $fillable = [
        "cp_id", "date", "headline", "media", "subject", "locale", "width", "height",
    ];

    protected $appends = [
        "media_url",
    ];

    public function getMediaUrlAttribute() {
        if ($this->media === null) {
            return null;
        }

        return Storage::disk("public")->url(config("services.canadian-press.storage.path") . $this->media);
    }
}
