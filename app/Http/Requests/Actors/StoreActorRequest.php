<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - StoreActorRequest.php
 */

namespace Neo\Http\Requests\Actors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Neo\Enums\Capability;

class StoreActorRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool {
		return Gate::allows(Capability::actors_create->value);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array {
		return [
			"is_group"  => ["required", "boolean"],
			"name"      => ["required", "string"],
			"email"     => ["required_unless:is_group,true", "email", Rule::unique("actors", "email")->withoutTrashed()],
			"locale"    => ["required", "string"],
			"enabled"   => ["sometimes", "boolean"],
			"parent_id" => ["required", "numeric", "exists:actors,id"],
			"roles"     => ["sometimes", "array", "distinct"],
			"roles.*"   => ["integer", "exists:roles,id"],
		];
	}
}
