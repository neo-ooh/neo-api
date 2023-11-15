<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MobileProductBracket.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int      $id
 * @property int      $mobile_product_id
 *
 * @property int      $budget_min
 * @property int|null $budget_max
 * @property int      $impressions_min
 * @property double   $cpm
 *
 * @property Carbon   $created_at
 * @property Carbon   $updated_at
 */
class MobileProductBracket extends Model {
	use HasPublicRelations;

	protected $table = "mobile_product_brackets";

	protected $primaryKey = "id";

	public function getPublicRelations() {
		return [];
	}
}
