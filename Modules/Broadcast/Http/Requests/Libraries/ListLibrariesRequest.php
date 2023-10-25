<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListLibrariesRequest.php
 */

namespace Neo\Modules\Broadcast\Http\Requests\Libraries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Modules\Broadcast\Models\Library;
use Neo\Rules\AccessibleActor;
use Neo\Rules\PublicRelations;

class ListLibrariesRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize(): bool {
		return Gate::allows(Capability::contents_schedule->value)
			|| Gate::allows(Capability::contents_edit->value)
			|| Gate::allows(Capability::libraries_edit->value);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules(): array {
		return [
			"formats"   => ["array"],
			"formats.*" => ["integer"],
			"layouts"   => ["array"],
			"layouts.*" => ["integer"],
			"with"      => ["array", new PublicRelations(Library::class)],

			"parent_id" => ["sometimes", new AccessibleActor()],
			"recursive" => ["sometimes", "boolean"],
		];
	}
}
