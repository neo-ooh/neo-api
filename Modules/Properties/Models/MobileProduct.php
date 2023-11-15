<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - MobileProduct.php
 */

namespace Neo\Modules\Properties\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Neo\Helpers\Relation;
use Neo\Models\Traits\HasPublicRelations;

/**
 * @property int          $id
 * @property string       $name_en
 * @property string       $name_fr
 *
 * @property Collection<> $brackets
 *
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 */
class MobileProduct extends Model {
	use HasPublicRelations;

	protected $table = "mobile_products";

	protected $primaryKey = "id";

	public function getPublicRelations() {
		return [
			"brackets" => new Relation(load: ["brackets"]),
		];
	}

	public function brackets() {
		return $this->hasMany(MobileProductBracket::class, "mobile_product_id", "id");
	}
}
