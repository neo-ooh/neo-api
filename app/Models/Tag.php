<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - Tag.php
 */

namespace Neo\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property int         $name
 * @property string|null $color
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class Tag extends Model {
	protected $table = "tags";

	protected $fillable = ["name"];

	protected $hidden = ["pivot"];
}
