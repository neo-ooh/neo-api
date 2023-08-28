<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowStatsRequest.php
 */

namespace Neo\Modules\Properties\Http\Requests\Public\Stats;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Exists;
use Neo\Models\Market;
use Neo\Modules\Properties\Models\Product;
use Neo\Modules\Properties\Models\ProductCategory;

class ShowStatsRequest extends FormRequest {
	public function rules(): array {
		return [
			"product_id"  => ["sometimes", new Exists(Product::class, "id")],
			"category_id" => ["sometimes", new Exists(ProductCategory::class, "id")],
			"market_id"   => ["sometimes", new Exists(Market::class, "id")],
		];
	}

	public function authorize(): bool {
		return true;
	}
}
