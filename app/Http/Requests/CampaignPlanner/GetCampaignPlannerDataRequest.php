<?php
/*
 * Copyright 2020 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GetCampaignPlannerDataRequest.php
 */

namespace Neo\Http\Requests\CampaignPlanner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Neo\Enums\Capability;
use Neo\Models\CampaignPlannerSave;
use Vinkla\Hashids\Facades\Hashids;

class GetCampaignPlannerDataRequest extends FormRequest {
    public function rules(): array {
        return [
            //
        ];
    }

    public function authorize(): bool {
        if (Gate::allows(Capability::tools_planning->value)) {
            return true;
        }

        if (!$this->route()?->hasParameter("campaignPlannerSave")) {
            return false;
        }

        return CampaignPlannerSave::query()
                                  ->where("id", "=", Hashids::decode($this->route("campaignPlannerSave"))[0] ?? null)
                                  ->exists();
    }
}
