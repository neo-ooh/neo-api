<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - ShowContractRequest.php
 */

namespace Neo\Http\Requests\Contracts;

use Auth;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Neo\Enums\Capability;
use Neo\Modules\Properties\Models\Contract;
use Neo\Rules\PublicRelations;

class ShowContractRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		/** @var Contract $contract */
		$contract = $this->route("contract");
		return $contract->salesperson_id === Auth::id() || Gate::allows(Capability::contracts_manage->value);
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		return [
			"with" => ["array", new PublicRelations(Contract::class)],
		];
	}
}
