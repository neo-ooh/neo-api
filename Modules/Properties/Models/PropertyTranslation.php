<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - PropertyTranslation.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $property_id
 * @property string $locale
 * @property string $description
 * @property Carbon $created_at
 * @property int    $created_by
 * @property Carbon $updated_at
 * @property int    $updated_by
 */
class PropertyTranslation extends Model {

	protected $table = "properties_translations";

	protected $primaryKey = null;

	public $incrementing = false;

	protected $fillable = [
		"property_id",
		"locale",
		"description",
	];
}
