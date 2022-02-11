<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Class NewsBackground
 *
 * @package Neo\Models
 *
 * @property int    $id
 * @property int    $category
 * @property string $network
 * @property int    $format_id
 * @property string $locale
 * @property string $path
 * @property Date   $created_at
 * @property Date   $updated_at
 */
class NewsBackground extends Model {
    use HasFactory;

    protected $table = "news_backgrounds";

    protected $appends = ["url"];

    protected $fillable = [
        "category",
        "network",
        "format_id",
        "locale",
        "path"
    ];

    protected $casts = [
        "category" => "integer"
    ];

    public static function boot(): void {
        parent::boot();

        static::deleting(function (NewsBackground $background) {
            Storage::disk("public")->delete($background->path);
        });
    }

    public function getUrlAttribute(): string {
        return Storage::disk("public")->url($this->path);
    }
}
