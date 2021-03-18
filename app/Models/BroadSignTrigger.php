<?php

namespace Neo\Models;

use Carbon\Carbon as Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BroadSignCriteria
 *
 * @package Neo\Models
 *
 * @property int $id
 * @property string $name
 * @property int $broadsign_trigger_id
 * @property Date $created_at
 * @property Date $updated_at
 */
class BroadSignTrigger extends Model
{
    use HasFactory;

    protected $table = "broadsign_triggers";

    protected $fillable = [
        "name",
        "broadsign_trigger_id",
    ];
}
