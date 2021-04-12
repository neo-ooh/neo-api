<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NewsBackground
 *
 * @package Neo\Models
 *
 * @property int $id
 * @property int $category
 * @property int $format_id
 * @property string $locale
 * @property string $path
 * @property Date $created_at
 * @property Date $updated_at
 */
class NewsBackground extends Model
{
    use HasFactory;

    protected $table = "news_backgrounds";

    protected $fillable = [
        "category",
        "format_id",
        "locale",
        "path"
    ];


}
