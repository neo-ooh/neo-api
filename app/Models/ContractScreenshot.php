<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Class ContractScreenshot
 *
 * @package Neo\Models
 *
 * @property integer       $id
 * @property integer       $burst_id
 * @property boolean       $is_locked
 * @property Date          $created_at
 * @property Date          $updated_at
 *
 * @property ContractBurst $burst
 *
 * @property string        $file_path
 * @property string        $url
 */
class ContractScreenshot extends Model {
    use HasFactory;

    protected $table = "contracts_screenshots";

    protected $casts = [
        "is_locked" => "boolean",
    ];

    protected $fillable = [
        "id",
        "burst_id",
        "is_locked",
        "created_at",
        "updated_at",
    ];

    protected $appends = [
        "url"
    ];

    public static function boot() {
        parent::boot();

        static::deleting(static function (ContractScreenshot $screenshot) {
            Storage::delete($screenshot->file_path);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function burst(): BelongsTo {
        return $this->belongsTo(ContractBurst::class, "burst_id", "id");
    }

    /*
    |--------------------------------------------------------------------------
    | Screenshot
    |--------------------------------------------------------------------------
    */

    public function getFilePathAttribute() {
        return "bursts/$this->burst_id/$this->id.jpg";
    }

    /**
     * @param resource $screenshot
     */
    public function store($screenshot) {
        // And store the request
        Storage::writeStream($this->file_path, $screenshot, ["visibility" => "public"]);
    }

    public function getUrlAttribute() {
        return Storage::url($this->file_path);
    }

}
