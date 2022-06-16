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

class GetCampaignPlannerTrafficRequest extends GetCampaignPlannerDataRequest {
    public function rules(): array {
        return [
            "date" => ["sometimes", "date"],
        ];
    }
}
