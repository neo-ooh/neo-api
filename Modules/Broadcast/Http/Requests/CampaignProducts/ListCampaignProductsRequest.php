<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListCampaignProductsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\CampaignProducts;

use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Product;
use Neo\Rules\PublicRelations;

class ListCampaignProductsRequest extends FormRequest {
	public function authorize(): bool {
		return Gate::allows(Capability::campaigns_edit->value);
	}

	public function rules(): array {
		return [
			"with" => ["array", new PublicRelations(Product::class)],
		];
	}
}
