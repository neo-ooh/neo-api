<?php

namespace Neo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class StaticCreative
 *
 * @package Neo\Models
 *
 * @property int creative_id
 * @property string extension
 * @property string checksum
 */
class StaticCreative extends Model
{
    use HasFactory;

    protected $table = "static_creatives";

    public $timestamps = false;

    protected $fillable = ["creative_id", "extension", "checksum"];
}
