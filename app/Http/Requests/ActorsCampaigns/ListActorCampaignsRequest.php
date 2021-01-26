<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <Valentin Dufois>
 *
 * @neo/api - UpdateActorRequest.php
 */

namespace Neo\Http\Requests\ActorsCampaigns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Neo\Models\Actor;

class ListActorCampaignsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool {
        // The current user can access the campaigns of anyone it has access to, AS WELL AS the campaigns from its parent, which is excluded from the default `hasAccessTo` method

        /** @var Actor $actor */
        $actor = Actor::findOrFail($this->route('actor'));
        Log::debug($this->route('actor'));

        return !$actor->is(Auth::user()) || $actor->id === Auth::user()->details->parent_id || Auth::user()->hasAccessTo($actor);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array {
        return [];
    }
}
