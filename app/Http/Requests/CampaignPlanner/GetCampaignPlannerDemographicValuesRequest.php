<?php
/*
 * Copyright 2023 (c) Neo-OOH - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Valentin Dufois <vdufois@neo-ooh.com>
 *
 * @neo/api - GetCampaignPlannerDemographicValuesRequest.php
 */

namespace Neo\Http\Requests\CampaignPlanner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Exists;
use Neo\Enums\Capability;
use Neo\Models\CampaignPlannerSave;
use Neo\Modules\Properties\Models\DemographicVariable;
use Vinkla\Hashids\Facades\Hashids;

class GetCampaignPlannerDemographicValuesRequest extends FormRequest {
    public function rules(): array {
        return [
            "variables"   => ["required", "array"],
            "variables.*" => [new Exists(DemographicVariable::class, "id")],
        ];
    }

    public function authorize(): bool {
        if (Gate::allows(Capability::planner_access->value)) {
            return true;
        }

        if (!$this->route()?->hasParameter("campaignPlannerSave")) {
            return false;
        }

        return CampaignPlannerSave::query()
                                  ->where("id", "=", Hashids::decode($this->route()
                                                                          ->originalParameter("campaignPlannerSave"))[0] ?? null)
                                  ->exists();
    }
}
