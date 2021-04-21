<?php

namespace Neo\Models;

use Carbon\Traits\Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ContractScreenshot
 *
 * @package Neo\Models
 *
 * @property integer $id
 * @property integer $burst_id
 * @property boolean $is_locked
 * @property Date $created_at
 * @property Date $updated_at
 *
 * @property Burst $burst
 *
 * @property string $file_path
 * @property string $url
 */
class ContractScreenshot extends Model
{
    use HasFactory;

    protected $table = "contracts_screenshots";

    protected $casts = [
        "is_locked" => "boolean",
    ];

    protected $fillable = [
        "id",
        "burst_id",
        "is_locked",
    ];

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

    public function getFilePathAttribute() {}
    public function getUrlAttribute() {}

}
