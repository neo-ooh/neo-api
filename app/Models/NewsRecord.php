<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    use HasFactory;

    protected $table = "news_records";

    protected $dates = ["date"];

    protected $fillable = [
        "cp_id", "date", "headline", "media", "subject", "locale", "width", "height"
    ];

    public function getMediaUrlAttribute() {
        if($this->media === null) {
            return null;
        }

        return config("services.canadian-press.storage.path") . $this->media;
    }
}
