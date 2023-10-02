<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - SyncCampaignProductsRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\CampaignProducts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Product;

class SyncCampaignProductsRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool {
		return Gate::allows(Capability::campaigns_edit->value);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array {
		return [
			"products"   => ["array"],
			"products.*" => ["integer", new Exists(Product::class, "id")],
		];
	}
}
