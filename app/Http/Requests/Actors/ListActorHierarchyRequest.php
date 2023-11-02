<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ListActorHierarchyRequest.php
 */

namespace Neo\Http\Requests\Actors;

use Illuminate\Foundation\Http\FormRequest;
use Neo\Models\Actor;
use Neo\Rules\PublicRelations;

class ListActorHierarchyRequest extends FormRequest {
	public function rules(): array {
		return [
			"compact" => ["sometimes", "boolean"],
			"with"    => ["array", new PublicRelations(Actor::class)],
		];
	}

	public function authorize(): bool {
		return true;
	}
}
